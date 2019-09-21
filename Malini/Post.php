<?php

namespace Malini;

use Closure;
use Exception;
use Malini\Helpers\AccessorRegistry;
use Malini\Helpers\DecoratorRegistry;
use Malini\Helpers\FieldsTree;
use Malini\Interfaces\PostDecoratorInterface;
use Malini\Interfaces\SerializableInterface;

class Post implements SerializableInterface {

    protected $attributes = [];

    protected $casts = [];

    protected $filters = [];

    protected $extensions = [];

    protected $show = [];

    protected $filter_fields = [];

    public $wp_post;

    public $custom_fields = null;

    public function __construct(\WP_Post $post) {
        $this->wp_post = $post;
    }

    public static function add(array &$target, string $source, $dest) {
        $target[$source] = $dest;
    }

    public static function multiAdd(array &$target, array $options) {
        foreach ($options as $option_src => $option_dest) {
            static::add($target, $option_src, $option_dest);
        }
    }

    public function addAttribute(string $source, $dest, bool $dont_add_to_show_fields = false) {
        static::add($this->attributes, $source, $dest);
        if (!$dont_add_to_show_fields) {
            $this->addShowFields(
                FieldsTree::parse($source)
            );
        }
        return $this;
    }

    public function addAttributes(array $attributes, bool $dont_add_to_show_fields = false) {
        foreach ($attributes as $attribute_src => $attribute_dest) {
            $this->addAttribute($attribute_src, $attribute_dest, $dont_add_to_show_fields);
        }
        return $this;
    }

    public function addRawAttribute(string $source, $dest) {
        return $this->addAttribute($source, $dest, true);
    }

    public function addRawAttributes(array $attributes) {
        return $this->addAttributes($attributes, true);
    }

    public function addFilter(string $source, $dest) {
        static::add($this->filters, $source, $dest);
        return $this;
    }

    public function addFilters(array $filters) {
        static::multiAdd($this->filters, $filters);
        return $this;
    }

    public function mapField(string $old_key, string $new_key) {
        if (isset($this->show[$old_key])) {
            $this->show[$new_key] = $this->show[$old_key];
            unset($this->show[$old_key]);
        }
    }

    public function map(array $map) {
        foreach ($map as $old_key => $new_key) {
            $this->mapField($old_key, $new_key);
        }
    }

    public function extend(string $method_name, $callback) {
        $this->extensions[$method_name] = $callback;
        return $this;
    }

    public function hasMethod(string $method_name) {
        return isset($this->extensions[$method_name]);
    }

    public function __call($method_name, $args) {
        if ($this->hasMethod($method_name)) {
            return $this->extensions[$method_name](...$args);
        }
        throw new Exception("Unknown method `$method_name` in class `" . static::class . "`");
    }

    public static function create(\WP_Post $post) {
        return new static($post);
    }

    public function loadCustom() {
        if (empty($this->custom_fields)) {
            $this->custom_fields = get_post_custom($this->wp_post->ID);
        }
        return $this;
    }

    public function getCustom(string $key, $single = null) {
        $this->loadCustom();
        if (!isset($this->custom_fields[$key])) {
            return null;
        }

        $data = [];
        foreach ($this->custom_fields[$key] as $field_value) {
            $data[] = \maybe_unserialize($field_value);
        }

        $auto_detect = is_null($single);

        if (!$auto_detect) {
            return ($single && !empty($data))
                ? $data[0]
                : $data;
        } else {
            return (!empty($data) && count($data) == 1)
                ? $data[0]
                : $data;
        }
    }

    public function getGroupedCustom(string $prefix, bool $auto_detect_single = false) {
        $this->loadCustom();
        $data = [];
        foreach (array_keys($this->custom_fields) as $key) {
            if (strpos($key, $prefix) === 0) {
                $data[substr($key, strlen($prefix))] = $this->getCustom($key, false);
                if ($auto_detect_single && count($data[substr($key, strlen($prefix))]) == 1) {
                    $data[substr($key, strlen($prefix))] = $data[substr($key, strlen($prefix))][0];
                }
            }
        }
        return $data;
    }

    public function getAllCustom(bool $auto_detect_single = false) {
        $this->loadCustom();
        $data = [];
        foreach (array_keys($this->custom_fields) as $key) {
            $data[$key] = $this->getCustom($key, false);
            if ($auto_detect_single && count($data[$key]) == 1) {
                $data[$key] = $data[$key][0];
            }
        }
        return $data;
    }

    public function hasAttribute($attribute_name) {
        return isset($this->attributes[$attribute_name]);
    }

    public function addShowField(array $field) {
        $this->show[$field['name']] = $field;
        if (!empty($field['filter'])) {
            $this->addFilter($field['name'], $field['filter']);
        }
        return $this;
    }

    public function addShowFields(array $fields) {
        foreach ($fields as $field) {
            $this->addShowField($field);
        }
        return $this;
    }

    public function show($fields) {
        $this->show = [];
        $this->addShowFields(
            FieldsTree::parse($fields)
        );
        return $this;
    }

    protected function parseFilters($raw_filters) {
        if ($raw_filters instanceof Closure) {
            return [
                $raw_filters
            ];
        }
        return (
            is_array($raw_filters)
                ? $raw_filters
                : (is_string($raw_filters)
                    ? explode('|', $raw_filters)
                    : []
                )
            );
    }

    protected function applyFilters($attr_name, $value) {
        $attr_filters = isset($this->filters[$attr_name])
            ? $this->filters[$attr_name]
            : [];

        $attr_filters = $this->parseFilters($attr_filters);
        foreach ($attr_filters as $filter_index => $filter) {
            if ($filter instanceof Closure) {
                $value = $filter($value, $attr_name, $this);
                continue;
            } else if (is_array($filter)) {
                $filter_name = array_shift($filter);
                $args = $filter;
            } else if (is_string($filter)) {
                $args = explode(':', $filter, 2);
                $filter_name = array_shift($args);
                $args = count($args) == 0
                    ? []
                    : explode(',', $args[0]);
            } else {
                $filter_name = $filter;
                $args = [];
            }

            if (!is_callable($filter_name)) {
                throw new Exception("Filter method `{$filter}` is not a valid callable");
            }

            $this_pos = array_search('$this', $args);
            if ($this_pos !== false) {
                $args[$this_pos] = $post;
            }
            array_unshift($args, $value);

            $value = $filter_name(...$args);
        }

        return $value;
    }

    protected static function filterArrayChildren(array $data, $filter_children) {
        $new_data = [];
        foreach ($filter_children as $attr) {
            $name = $attr['name'];
            $alias = $attr['alias'];
            $children = $attr['children'];
            $new_data[$alias] = empty($children)
                ? $data[$name]
                : static::filterChildren($data[$name], $children);
        }
        return $new_data;
    }

    protected static function filterObjectChildren($data, $filter_children) {
        $new_data = new \StdClass();
        foreach ($filter_children as $attr) {
            $name = $attr['name'];
            $alias = $attr['alias'];
            $children = $attr['children'];
            $new_data->{$alias} = empty($children)
                ? $data->{$name}
                : static::filterChildren($data->{$name}, $children);
        }
        return $new_data;
    }

    protected static function filterChildren($data, array $filter_children) {
        if (!is_object($data) && !is_array($data)) {
            throw new Exception("Cannot filter children of `" . gettype($data) . "` variable");
        }

        if (is_object($data)) {
            return static::filterObjectChildren($data, $filter_children);
        }

        if (!is_sequential_array($data)) {
            return static::filterArrayChildren($data, $filter_children);
        }

        $new_data = [];
        foreach ($data as $index => $data_elemen) {
            $index_filter_children = FieldsTree::getIndexedFilter($index, $filter_children);
            $new_data[] = empty($index_filter_children)
                ? $data_elemen
                : static::filterChildren($data_elemen, $index_filter_children);
        }

        return $new_data;
    }

    protected function mapAttributesOnShow() {
        $this->show = [];
        foreach ($this->attributes as $key => $_value) {
            $this->show[$key] = [
                'name' => $key,
                'alias' => $key,
                'filter' => null,
                'children' => []
            ];
        }
        return $this->show;
    }

    public function toObject() {
        $show_attrs = (empty($this->show))
            ? $this->mapAttributesOnShow()
            : $this->show;
        $to_show = new \StdClass();
        foreach ($show_attrs as $attr) {
            $attr_name = $attr['name'];
            $attr_alias = $attr['alias'];
            $attr_children = $attr['children'];
            $to_show->{$attr_alias} = $this->applyFilters(
                $attr_name,
                AccessorRegistry::retrieve($this, $this->attributes[$attr_name])
            );
            if (!empty($attr_children)) {
                $to_show->{$attr_alias} = static::filterChildren($to_show->{$attr_alias}, $attr_children);
            }
        }

        return $to_show;
    }

    public function jsonSerialize() {
        return json_encode($this->toObject(), JSON_NUMERIC_CHECK);
    }

    public function toJson() {
        return $this->jsonSerialize();
    }

    public function toArray() : array {
        return json_decode($this->jsonSerialize(), true);
    }

    public function decorate(string $decorator, array $options = []) : Post {
        $decorator_namespace = DecoratorRegistry::get($decorator);

        if (!in_array(PostDecoratorInterface::class, class_implements($decorator_namespace))) {
            throw new Exception("`$decorator_namespace` does not implements the `PostDecoratorInterface`");
        }

        new $decorator_namespace($this, $options);
        return $this;
    }

}

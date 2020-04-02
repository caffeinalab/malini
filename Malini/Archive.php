<?php

namespace Malini;

use Closure;
use Exception;
use Malini\Interfaces\SerializableInterface;

class Archive implements SerializableInterface, \ArrayAccess, \Countable, \IteratorAggregate
{
    protected $posts = [];

    protected $static_attributes = [];

    protected $pages_range = 2;

    protected $pagination_data = null;

    public function __construct(array $posts = [])
    {
        foreach ($posts as $post) {
            $this->posts[] = new Post($post);
        }
    }

    public function paginate(int $pages_range = 2)
    {
        global $paged, $wp_query;

        $this->curr_page = (empty($paged))
          ? 1
          : (int) $paged;

        $this->total = (int) $wp_query->found_posts;
        $this->per_page = (int) $wp_query->query_vars['posts_per_page'];
        $this->pages = (int) $wp_query->max_num_pages;
        if ($this->pages < 1) {
            $this->pages = 1;
        }

        $this->pages_range = $pages_range;

        $this->pagination_data = new \StdClass();

        $this->pagination_data->total = $this->total;
        $this->pagination_data->per_page = $this->per_page;
        $this->pagination_data->current_page = $this->curr_page;
        $this->pagination_data->last_page = $this->pages;
        $this->pagination_data->next_page = $this->curr_page < $this->pages ? $this->curr_page + 1 : null;
        $this->pagination_data->prev_page = $this->curr_page > 1 ? $this->curr_page - 1 : null;
        $this->pagination_data->current_link = get_pagenum_link($this->curr_page);
        $this->pagination_data->last_link = get_pagenum_link($this->pages);
        $this->pagination_data->next_link = $this->curr_page < $this->pages ? get_pagenum_link($this->curr_page + 1) : null;
        $this->pagination_data->prev_link = $this->curr_page > 1 ? get_pagenum_link($this->curr_page - 1) : null;
        $this->pagination_data->pages = [];

        if ($this->pages == 1) {
            return $this;
        }

        $pages_range = range(
            max(1, $this->curr_page - $this->pages_range),
            min($this->pages, $this->curr_page + $this->pages_range)
        );
        foreach ($pages_range as $page) {
            $this->pagination_data->pages[$page] = get_pagenum_link($page);
        }

        return $this;
    }

    public function paginationData()
    {
        if (empty($this->pagination_data)) {
            throw new Exception('This archive is not paginated');
        }

        return $this->pagination_data;
    }

    public function setPagesRange(int $value)
    {
        $this->pages_range = $value;

        return $this;
    }

    public function getPagesRange()
    {
        return $this->pages_range;
    }

    public static function create(array $posts)
    {
        return new static($posts);
    }

    // ArrayAccess
    public function offsetExists($offset): bool
    {
        return isset($this->posts[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->posts[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->posts[] = $value;
        } else {
            $this->posts[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->posts[$offset]);
    }

    // Countable
    public function count(): int
    {
        return count($this->posts);
    }

    // IteratorAggregate
    public function getIterator()
    {
        foreach ($this->posts as $post) {
            yield $post;
        }
    }

    protected function applyToAllPosts(string $method, array $args = [])
    {
        foreach ($this->posts as $post) {
            if (is_string($method)) {
                $post->{$method}(...$args);
            } elseif ($method instanceof Closure) {
                $method($post, ...$args);
            } else {
                throw new Exception("Unknown method `{$method}` to apply to a list of posts");
            }
        }

        return $this;
    }

    public function addAttribute(string $source, $dest)
    {
        return $this->applyToAllPosts('addAttribute', [$source, $dest]);
    }

    public function addAttributes(array $attributes)
    {
        return $this->applyToAllPosts('addAttributes', [$attributes]);
    }

    public function addStaticAttribute(string $key, $value)
    {
        $this->static_attributes[$key] = $value;

        return $this;
    }

    public function addStaticAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->addStaticAttribute($key, $value);
        }

        return $this;
    }

    public function addStaticPostsAttribute(string $key, $value)
    {
        return $this->applyToAllPosts('addStaticAttribute', [$key, $value]);
    }

    public function addStaticPostsAttributes(array $attributes)
    {
        return $this->applyToAllPosts('addStaticAttributes', [$attributes]);

        return $this;
    }

    public function addFilter(string $source, $dest)
    {
        return $this->applyToAllPosts('addFilter', [$source, $dest]);
    }

    public function addFilters(array $filters)
    {
        return $this->applyToAllPosts('addFilters', [$filters]);
    }

    public function mapField(string $old_key, string $new_key)
    {
        return $this->applyToAllPosts('mapField', [$old_key, $new_key]);
    }

    public function map(array $map)
    {
        return $this->applyToAllPosts('map', [$map]);
    }

    public function addShowField(array $field)
    {
        return $this->applyToAllPosts('addShowField', [$field]);
    }

    public function addShowFields(array $fields)
    {
        return $this->applyToAllPosts('addShowFields', [$fields]);
    }

    public function show($fields)
    {
        return $this->applyToAllPosts('show', [$fields]);
    }

    public function decorate($decorator_or_callback, array $options = []): Archive
    {
        return $this->applyToAllPosts('decorate', [$decorator_or_callback, $options]);
    }

    public function toObject()
    {
        $data = [
            'posts' => array_map(
                function ($post) {
                    return $post->toObject();
                },
                $this->posts
            ),
            'pagination' => $this->pagination_data,
        ];

        foreach ($this->static_attributes as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    public function jsonSerialize()
    {
        return json_encode($this->toObject(), JSON_NUMERIC_CHECK);
    }

    public function toJson()
    {
        return $this->jsonSerialize();
    }

    public function toArray(): array
    {
        return json_decode($this->jsonSerialize(), true);
    }
}

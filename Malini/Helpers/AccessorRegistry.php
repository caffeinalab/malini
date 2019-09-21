<?php

namespace Malini\Helpers;

use Malini\Post;
use Malini\Interfaces\AccessorInterface;

/**
 * Accessors are utilities that helps retrieve data in a structured way.
 * They can be described using simple strings with the following syntax (curly
 * brackets indicates placeholders):
 * 
 * @{accessor_name}:{param_1},{param_2},...
 * 
 * - {accessor_name}: is the name of the registered accessor;
 * - {param_N}: is the N-th param to pass to the accessor.
 * 
 * If no {accessor_name} is specified, the `BaseAccessor` will be used.
 * 
 * Examples:
 * 
 * - 'post_title'           // retrieves the post_title, using the BaseAccessor;
 * - '@base:post_title'     // retrieves the post_title, using the BaseAccessor;
 * - '@meta:_edit_lock'     // retrieves the _edit_lock post meta MetaAccessor.
 */
class AccessorRegistry
{

    protected static $registered_accessors = [];

    public static function has(string $name) {
        return isset(static::$registered_accessors[$name]);
    }

    public static function get(string $name) {
        if (!static::has($name)) {
            throw new \Exception("Unknown `{$name}` accessor");
        }
        return static::$registered_accessors[$name];
    }

    public static function register(string $name, $accessor_class_name) {
        if (!in_array(AccessorInterface::class, class_implements($accessor_class_name))) {
            throw new \Exception("`{$accessor_class_name}` does not implements the `AccessorInterface`");
        }
        static::$registered_accessors[$name] = new $accessor_class_name();
    }

    public static function parse(string $source) {
        if (strpos($source, '@') === 0) {
            $pieces = explode(':', substr($source, 1), 2);
            $accessor = array_shift($pieces);
            $params = empty($pieces)
                ? []
                : explode(',', $pieces[0]);
        } else {
            $accessor = 'base';
            $params = [ $source ];
        }

        return [ $accessor, $params ];
    }

    public static function access(Post $post, string $accessor_name, array $params) {
        $accessor = static::get($accessor_name);
        return $accessor->retrieve($post, ...$params);
    }

    public static function retrieve(Post $post, $source) {
        if (is_callable($source)) {
            return $source($post);
        }
        list($accessor_name, $params) = static::parse($source);
        return static::access($post, $accessor_name, $params);
    }

}
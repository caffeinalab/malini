<?php

namespace Malini\Helpers;

use Malini\Post;
use Malini\Interfaces\PostDecoratorInterface;

class DecoratorRegistry
{

    protected static $registered_decorators = [];

    public static function has(string $name) {
        return isset(static::$registered_decorators[$name]);
    }

    public static function get(string $name) : string {
        if (!static::has($name)) {
            throw new \Exception("Unknown `{$name}` decorator");
        }
        return static::$registered_decorators[$name];
    }

    public static function register(string $name, $decorator_class_name) {
        if (!in_array(PostDecoratorInterface::class, class_implements($decorator_class_name))) {
            throw new \Exception("`{$decorator_class_name}` does not implements the `PostDecoratorInterface`");
        }
        static::$registered_decorators[$name] = $decorator_class_name;
    }

}
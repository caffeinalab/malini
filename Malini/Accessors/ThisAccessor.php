<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Interfaces\AccessorInterface;

/**
 * The `this` accessor calls a method of the underlying Malini\Post; it is
 * possible to add more methods to an instance of the Malini\Post using the
 * `extend` method.
 * 
 * Syntax:
 * 
 * @this:{method_name}
 * 
 * - {method_name}: name of the method to call.
 */
class ThisAccessor implements AccessorInterface
{

    public function retrieve(Post $post, ...$arguments) {
        $method_name = $arguments[0];

        return (method_exists($post, $method_name) || $post->hasMethod($method_name))
            ? $post->{$method_name}(...$arguments)
            : null;
    }

}
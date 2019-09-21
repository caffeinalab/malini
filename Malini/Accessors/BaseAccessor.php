<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Interfaces\AccessorInterface;

/**
 * The `base` accessor retrieve an attribute of the wp_post.
 * 
 * Syntax:
 * 
 * @base:{attribute_name},{default}
 * 
 * - {attribute_name}: name of the wanted attribute;
 * - {default}: default value, if the attribute is empty.
 */
class BaseAccessor implements AccessorInterface
{

    public function retrieve(Post $post, ...$arguments) {
        $target = $arguments[0];
        $default = isset($arguments[1]) ? $arguments[1] : null;

        $wp_post = $post->wp_post;

        return isset($wp_post->{$target})
            ? $wp_post->{$target}
            : $default;
    }

}
<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Interfaces\AccessorInterface;

/**
 * The `meta` accessor retrieve post meta related to the post.
 * 
 * Syntax:
 * 
 * @meta:{meta_key},{default},{single}
 * 
 * - {meta_key}: the wanted post meta key;
 * - {default}: default value, if no post meta is found;
 * - {single}: specify if the save value is single or a list of values; if
 *             nothing is specified, it will try to guess it.
 */
class MetaAccessor implements AccessorInterface
{

    public function retrieve(Post $post, ...$arguments) {
        $target = $arguments[0];
        $default = isset($arguments[1]) ? $arguments[1] : null;
        $single = isset($arguments[2])
            ? (bool)$arguments[2]
            : null;

        $value = $post->getCustom($target, $single);

        return !empty($value)
            ? $value
            : $default;
    }

}
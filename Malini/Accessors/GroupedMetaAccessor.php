<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Interfaces\AccessorInterface;

/**
 * The `grouped_meta` accessor retrieve a group of meta with the same prefix.
 * 
 * Syntax:
 * 
 * @grouped_meta:{meta_prefix},{auto_detect_single},{default}
 * 
 * - {meta_prefix}: prefix of the wanted post metas;
 * - {auto_detect_single}: post metas are retrieved by default as arrays; if
 *                         this option is set to `true`, it will guess if a meta
 *                         is single or multiple (defaults to `false`);
 * - {default}: default value, if no post metas are found.
 */
class GroupedMetaAccessor implements AccessorInterface
{

    public function retrieve(Post $post, ...$arguments) {
        $prefix = $arguments[0];
        $auto_detect_single = isset($arguments[1])
            ? $arguments[1]
            : false;
        $default = isset($arguments[2]) ? $arguments[2] : null;

        $value = $post->getGroupedCustom($prefix, $auto_detect_single);

        return !empty($value)
            ? maybe_unserialize($value)
            : $default;
    }

}
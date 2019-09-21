<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Interfaces\AccessorInterface;

/**
 * The `all_meta` accessor retrieve all post meta related to the post.
 * 
 * Syntax:
 * 
 * @all_meta:{auto_detect_single}
 * 
 * - {auto_detect_single}: post metas are retrieved by default as arrays; if
 *                         this option is set to `true`, it will guess if a meta
 *                         is single or multiple (defaults to `false`);
 */
class AllMetaAccessor implements AccessorInterface
{

    public function retrieve(Post $post, ...$arguments) {
        $auto_detect_single = isset($arguments[0])
            ? (bool)$arguments[0]
            : false;

        return $post->loadCustom()->getAllCustom($auto_detect_single);
    }

}
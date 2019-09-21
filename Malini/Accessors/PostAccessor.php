<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Helpers\AccessorRegistry;
use Malini\Interfaces\AccessorInterface;

/**
 * The `post` accessor retrieve a `Malini\Post`.
 * 
 * Syntax:
 * 
 * @post:{post_id_or_meta_key},{decorators}
 * 
 * - {post_id_or_meta_key}: post numeric id or string meta key containing the
 *                          wanted post; also an accessor definition can be
 *                          passed (just keep in mind that an int is expected);
 * - {decorators}: list of registered decorators to apply; they must be divided
 *                 by pipe; no options can be passed (filters can be applied
 *                 at that purpose).
 */
class PostAccessor implements AccessorInterface
{

    public function retrieve(Post $post, ...$arguments) {
        $post_id_or_meta_key = $arguments[0];
        $decorators = isset($arguments[1])
            ? explode('|', $arguments[1])
            : [];

        if (ctype_digit((string)$post_id_or_meta_key)) {
            $post_id = (int)$post_id_or_meta_key;
        } else if (is_string($post_id_or_meta_key) && strpos($post_id_or_meta_key, '@') == 0) {
            list($accessor_name, $params) = AccessorRegistry::parse($post_id_or_meta_key);
            $post_id = (int)AccessorRegistry::access($post, $accessor_name, $params);
        } else {
            $post_id = (int)get_post_meta($post->wp_post->ID, $post_id_or_meta_key, true);
        }

        if (empty($post_id)) {
            return null;
        }
        
        $post = malini_post($post_id);
        foreach ($decorators as $decorator) {
            $post = $post->decorate($decorator);
        }

        return $post->toObject();
    }

}
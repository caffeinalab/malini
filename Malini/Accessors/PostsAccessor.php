<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Helpers\AccessorRegistry;
use Malini\Interfaces\AccessorInterface;

/**
 * The `posts` accessor retrieve a list of `Malini\Post`.
 * 
 * Syntax:
 * 
 * @posts:{post_id_or_meta_key_1}, ... ,{post_id_or_meta_key_n},{decorators}
 * 
 * - {post_id_or_meta_key_n}: post numeric id or string meta key containing the
 *                            wanted post; n of them can be passed;
 * - {decorators}: list of registered decorators to apply to all retrieved
 *                 posts; they must be divided by pipe; no options can be passed
 *                 (filters can be applied at that purpose); if no decorator are
 *                 wanted, the `false` string can be passed.
 */
class PostsAccessor implements AccessorInterface
{

    public function retrieve(Post $post, ...$arguments) {
        $decorators = array_pop($arguments);
        if ($decorators === 'false') {
            $decorators = '';
        }

        $wp_post = $post->wp_post;

        $posts = [];
        $post_ids = [];

        foreach ($arguments as $argument) {
            if (ctype_digit((string)$argument)) {
                $post_ids[] = (int)$argument;
            } else {
                $meta_value = get_post_meta($wp_post->ID, $argument, true);
                if (is_array($meta_value)) {
                    $post_ids = array_merge(
                        $post_ids,
                        array_map(
                            'int',
                            $meta_value
                        )
                    );
                } else if (is_string($meta_value)) {
                    $meta_value = array_map(
                        'int',
                        explode(',', $meta_value)
                    );
                } else {
                    $meta_value = [ (int)$meta_value ];
                }

                $post_ids = array_merge($post_ids, $meta_value);
            }
        }

        foreach ($post_ids as $post_id) {
            $posts[] = AccessorRegistry::access($post, 'post', [ $post_id, $decorators ]);
        }

        return $posts;
    }

}
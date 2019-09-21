<?php

namespace Malini\Decorators;

use Malini\Post;
use Malini\Abstracts\PostDecorator;
use Malini\Interfaces\PostDecoratorInterface;

/**
 * The `all_meta` decorator adds to the `Malini\Post` all the post-meta related to this post.
 * 
 * Attributes added:
 * - `post_meta`: an associative array containing all the post-meta values, indexed by their `meta_key`.
 * 
 * Options:
 * - `filter`: the attributes we want to retrieve;
 * - `automerge_fields`: boolean value that specify if we want to avoid creating the `post_meta` attribute and explodes all the custom fields at the top level; defaults to `false`;
 * - `auto_detect_single`: boolean value that specify if we want to try and automatically detect if a post-meta is a single value or not; defaults to `false`.
 */
class WithAllMetas extends PostDecorator implements PostDecoratorInterface {

    public function decorate(Post $post, array $options = []) : Post {
        $automerge_fields = $this->getConfig('automerge_fields', $options, false);
        $auto_detect_single = $this->getConfig('auto_detect_single', $options, false);

        if ($automerge_fields) {
            $fields_keys = array_keys($post->loadCustom()->getAllCustom($auto_detect_single));
            foreach ($fields_keys as $key) {
                $post->addAttribute(
                    $key,
                    '@meta:' . $key
                );
            }
        } else {
            $post->addRawAttributes([
                'post_meta' => '@all_meta:' . ($auto_detect_single ? 1 : 0)
            ]);
            $fields_keys = [ 'post_meta' ];
        }

        $this->filterConfig(
            $post,
            $options,
            $fields_keys
        );

        return $post;
    }

}
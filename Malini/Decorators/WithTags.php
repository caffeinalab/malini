<?php

namespace Malini\Decorators;

use Malini\Post;
use Malini\Abstracts\PostDecorator;
use Malini\Interfaces\PostDecoratorInterface;

/**
 * The `tags` decorator adds to the `Malini\Post` the list of tags related to the current post.
 * 
 * Attributes added:
 * - `tags`: an array containing this post tags.
 * 
 * Options:
 * - `filter`: the attributes we want to retrieve
 */
class WithTags extends PostDecorator implements PostDecoratorInterface {

    public function decorate(Post $post, array $options = []) : Post {
        $wp_post = $post->wp_post;

        $post->addRawAttributes([
            'tags'   => function() use ($wp_post) {
                $tags = wp_get_post_tags($wp_post->ID);
                return $tags;
            }
        ]);

        $this->filterConfig(
            $post,
            $options,
            [ 'tags' ]
        );

        return $post;
    }

}
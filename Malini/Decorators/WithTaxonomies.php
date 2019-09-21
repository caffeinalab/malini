<?php

namespace Malini\Decorators;

use Malini\Post;
use Malini\Abstracts\PostDecorator;
use Malini\Interfaces\PostDecoratorInterface;

/**
 * The `taxonomies` decorator adds to the `Malini\Post` the list of taxonomies related to the current post.
 * 
 * Attributes added:
 * - `taxonomies`: an array containing this post taxonomies;
 * - `terms`: an array containing this post terms.
 * 
 * Options:
 * - `filter`: the attributes we want to retrieve
 */
class WithTaxonomies extends PostDecorator implements PostDecoratorInterface {

    public function decorate(Post $post, array $options = []) : Post {
        $wp_post = $post->wp_post;

        $post->addRawAttributes([
            'taxonomies'   => function() use ($wp_post) {
                return get_post_taxonomies($wp_post);
            },
            'terms' => function() use ($wp_post) {
                return wp_get_post_terms($wp_post->ID);
            }
        ]);

        $this->filterConfig(
            $post,
            $options,
            [ 'taxonomies', 'terms' ]
        );

        return $post;
    }

}
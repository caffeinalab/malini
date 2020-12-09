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
class WithTaxonomies extends PostDecorator implements PostDecoratorInterface
{
    public function decorate(Post $post, array $options = []): Post
    {
        $wp_post = $post->wp_post;
        $post_taxonomies = get_post_taxonomies($wp_post);

        $post->addRawAttributes([
            'taxonomies' => function () use ($post_taxonomies) {
                return $post_taxonomies;
            },
            'terms' => function () use ($wp_post, $post_taxonomies, $options) {
                $terms = [];
                foreach ($post_taxonomies as $taxonomy) {
                    $terms_by_taxonomy = get_the_terms($wp_post->ID, $taxonomy);
                    if (is_array($terms_by_taxonomy)) {
                        if (isset($options['options']) && in_array('show_taxonomy_key', $options['options'])) {
                            $terms[$taxonomy] = $terms_by_taxonomy;
                        } else {
                            $terms = array_merge($terms, $terms_by_taxonomy);
                        }
                    }
                }

                return $terms;
            },
        ]);

        $this->filterConfig(
            $post,
            $options,
            ['taxonomies', 'terms']
        );

        return $post;
    }
}

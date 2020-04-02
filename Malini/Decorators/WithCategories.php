<?php

namespace Malini\Decorators;

use Malini\Post;
use Malini\Abstracts\PostDecorator;
use Malini\Interfaces\PostDecoratorInterface;

/**
 * The `categories` decorator adds to the `Malini\Post` the list of categories related to the current post.
 *
 * Attributes added:
 * - `categories`: an array containing this post categories.
 *
 * Options:
 * - `filter`: the attributes we want to retrieve
 */
class WithCategories extends PostDecorator implements PostDecoratorInterface
{
    public function decorate(Post $post, array $options = []): Post
    {
        $wp_post = $post->wp_post;

        $post->addRawAttributes([
            'categories' => function () use ($wp_post) {
                return get_the_terms($wp_post->ID, 'category');
            },
        ]);

        $this->filterConfig(
            $post,
            $options,
            ['categories']
        );

        return $post;
    }
}

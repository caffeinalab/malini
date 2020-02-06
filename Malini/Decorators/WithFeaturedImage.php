<?php

namespace Malini\Decorators;

use Malini\Post;
use Malini\Abstracts\PostDecorator;
use Malini\Interfaces\PostDecoratorInterface;

/**
 * The `featuredimage` decorator enrich the `Malini\Post` with the data regarding the feature selected for this post.
 *
 * Attributes added:
 * - `thumbnail`: the featured image data in its `thumbnail` size;
 * - `featuredimage`: the featured image data in its `full` size.
 *
 * Options:
 * - `filter`: the attributes we want to retrieve
 */
class WithFeaturedImage extends PostDecorator implements PostDecoratorInterface
{
    public function decorate(Post $post, array $options = []): Post
    {
        $post->addRawAttributes([
            'featuredimage' => '@media',
        ]);

        $this->filterConfig(
            $post,
            $options,
            ['featuredimage']
        );

        return $post;
    }
}

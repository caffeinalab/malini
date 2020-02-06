<?php

namespace Malini\Abstracts;

use Malini\Post;
use Malini\Interfaces\PostDecoratorInterface;
use Malini\Helpers\FieldsTree;

abstract class PostDecorator implements PostDecoratorInterface
{
    public function __construct(Post $post, array $options = [])
    {
        $this->decorate($post, $options);

        return $post;
    }

    public function decorate(Post $post, array $_options = []): Post
    {
        return $post;
    }

    public function getConfig(string $option_name, array $options, $default)
    {
        return isset($options[$option_name])
            ? $options[$option_name]
            : $default;
    }

    public function filterConfig(Post $post, array $options = [], array $default = [])
    {
        $fields = $this->getConfig('filter', $options, $default);

        $post->addShowFields(
            FieldsTree::parse($fields)
        );
    }
}

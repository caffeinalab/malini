<?php

namespace Malini\Interfaces;

use Malini\Post;

interface PostDecoratorInterface {

    public function decorate(Post $post, array $options) : Post;

}
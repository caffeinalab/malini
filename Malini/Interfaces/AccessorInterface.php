<?php

namespace Malini\Interfaces;

use Malini\Post;

interface AccessorInterface
{

    public function retrieve(Post $post, ...$arguments);

}
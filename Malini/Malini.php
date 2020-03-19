<?php

namespace Malini;

use Malini\Helpers\AccessorRegistry;
use Malini\Helpers\DecoratorRegistry;

class Malini
{
    public const VERSION = '1.0.1';

    protected static $instance = null;

    protected $booted = false;

    private function __construct()
    {
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        // Accessors

        $this->registerAccessor('base', \Malini\Accessors\BaseAccessor::class);
        $this->registerAccessor('this', \Malini\Accessors\ThisAccessor::class);
        $this->registerAccessor('meta', \Malini\Accessors\MetaAccessor::class);
        $this->registerAccessor('all_meta', \Malini\Accessors\AllMetaAccessor::class);
        $this->registerAccessor('grouped_meta', \Malini\Accessors\GroupedMetaAccessor::class);
        $this->registerAccessor('media', \Malini\Accessors\MediaAccessor::class);
        $this->registerAccessor('post', \Malini\Accessors\PostAccessor::class);
        $this->registerAccessor('posts', \Malini\Accessors\PostsAccessor::class);

        do_action('malini_register_accessors');

        // Decorators

        $this->registerDecorator('post', \Malini\Decorators\WithPostData::class);
        $this->registerDecorator('all_meta', \Malini\Decorators\WithAllMetas::class);
        $this->registerDecorator('featuredimage', \Malini\Decorators\WithFeaturedImage::class);
        $this->registerDecorator('tags', \Malini\Decorators\WithTags::class);
        $this->registerDecorator('categories', \Malini\Decorators\WithCategories::class);
        $this->registerDecorator('taxonomies', \Malini\Decorators\WithTaxonomies::class);

        do_action('malini_register_decorators');

        // Init
        do_action('malini_init');

        $this->booted = true;
    }

    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new Malini();
        }

        return static::$instance;
    }

    public function registerAccessor(string $name, string $namespace)
    {
        AccessorRegistry::register($name, $namespace);

        return $this;
    }

    public function registerDecorator(string $name, string $namespace)
    {
        DecoratorRegistry::register($name, $namespace);

        return $this;
    }

    public function post(\WP_post $post)
    {
        return Post::create($post);
    }

    public function archive(array $posts)
    {
        return Archive::create($posts);
    }
}

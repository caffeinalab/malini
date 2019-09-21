<?php

namespace Malini\Decorators;

use Malini\Post;
use Malini\Abstracts\PostDecorator;
use Malini\Interfaces\PostDecoratorInterface;

/**
 * The `post` decorator enrich the `Malini\Post` with all the basic info about
 * a `WP_Post`.
 * 
 * Attributes added:
 * - `id`: the `WP_Post->ID`;
 * - `title`: the `WP_Post->post_title`;
 * - `content`: the `WP_Post->post_content`;
 * - `content_filtered`: the `WP_Post->post_content_filtered`;
 * - `status`: the `WP_Post->post_status`;
 * - `excerpt`: the `WP_Post->post_excerpt`;
 * - `created_at`: the `WP_Post->post_date`;
 * - `slug`: the `WP_Post->post_name`;
 * - `updated_at`: the `WP_Post->post_modified`;
 * - `parent_id`: the `WP_Post->post_parent`;
 * - `order`: the `WP_Post->menu_order`;
 * - `posttype`: the `WP_Post->post_type`;
 * - `permalink`: the `WP_Post` permalink.
 * 
 * Options:
 * - `filter`: the attributes we want to retrieve.
 */
class WithPostData extends PostDecorator implements PostDecoratorInterface {

    public function decorate(Post $post, array $options = []) : Post {
        $wp_post = $post->wp_post;

        $attributes_list = [
            'id'                => 'ID',
            'title'             => 'post_title',
            'content'           => 'post_content',
            'content_filtered'  => 'post_content_filtered',
            'status'            => 'post_status',
            'excerpt'           => 'post_excerpt',
            'created_at'        => 'post_date',
            'slug'              => 'post_name',
            'updated_at'        => 'post_modified',
            'parent_id'         => 'post_parent',
            'order'             => 'menu_order',
            'posttype'          => 'post_type',
            'permalink'         => function() use($wp_post) {
                return get_permalink($wp_post->ID);
            }
        ];

        $post->addRawAttributes($attributes_list);

        $this->filterConfig(
            $post,
            $options,
            array_keys($attributes_list)
        );

        return $post;
    }

}
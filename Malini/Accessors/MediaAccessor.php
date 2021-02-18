<?php

namespace Malini\Accessors;

use Malini\Post;
use Malini\Interfaces\AccessorInterface;

/**
 * The `media` accessor retrieves media related to the post.
 *
 * Syntax:
 *
 * @media:{size},{media_meta_key_or_id},{media_property_key}
 *
 * - {size}: media size (full, thumbnail, etc.; custom sizes are accepted as
 *           long as they have been registered);
 * - {media_meta_key_or_id}: if the id of the wanted media is saved inside a
 *                           post meta, we can specify here the wanted meta key;
 *                           if we know directly the id of the media, we can
 *                           pass that; if `media_meta_key_or_id` is not
 *                           specified or the string `this` is passed, the post
 *                           thumbnail is used;
 * - {media_property_key}: if we want only a specific property of the media, we
 *                         can specify it as the third parameter; otherwise it
 *                         will return all the available data of the retrieved
 *                         media.
 */
class MediaAccessor implements AccessorInterface
{
    public function retrieve(Post $post, ...$arguments)
    {
        $wp_post = $post->wp_post;

        $media_id = (isset($arguments[1]) && $arguments[1] !== 'this')
        ? (int) get_post_meta($wp_post->ID, $arguments[1], true)
        : get_post_thumbnail_id($wp_post->ID);

        if (empty($media_id)) {
            return null;
        }

        $media = [
          'meta' => get_post($media_id),
        ];
        if (isset($arguments[0])) {
            $sizes = explode($arguments[0]);
        } else {
            $sizes = get_intermediate_image_sizes();
            $sizes[] = 'full';
        }

        foreach ($sizes as $size) {
            $media[$size] = wp_get_attachment_image_src($media_id, $size);
            $media[$size][3] = $media[$size][1] != 0 ? $media[$size][2] / $media[$size][1] : 0;
        }

        return $media;
    }
}

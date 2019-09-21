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

    protected static $media_cache = [];

    public function getThumbnailData($thumbnail_id) {
        $thumbnail_key = 't_' . $thumbnail_id;
        if (!isset(static::$media_cache[$thumbnail_key])) {
            static::$media_cache[$thumbnail_key] = (array)wp_prepare_attachment_for_js($thumbnail_id);
        }
        return static::$media_cache[$thumbnail_key];
    }

    public function retrieve(Post $post, ...$arguments) {
        $wp_post = $post->wp_post;

        $size = isset($arguments[0])
            ? $arguments[0]
            : 'full';

        $thumbnail_id = (isset($arguments[1]) && $arguments[1] !== 'this')
            ? (int)get_post_meta($wp_post->ID, $arguments[1], true)
            : get_post_thumbnail_id($wp_post->ID);

        if (empty($thumbnail_id)) {
            return null;
        }

        $media_property_key = isset($arguments[2])
            ? (string)$arguments[2]
            : null;

        $thumbnail_data = static::getThumbnailData($thumbnail_id);
        if (!isset($thumbnail_data['sizes'][$size])) {
            $size = 'full';
        }

        if (empty($thumbnail_data)) {
            return null;
        }

        $selected_size = $thumbnail_data['sizes'][$size];

        $media = [
            'name'              => $thumbnail_data['name'],
            'filename'          => $thumbnail_data['filename'],
            'alt'               => $thumbnail_data['alt'],
            'caption'           => $thumbnail_data['caption'],
            'description'       => $thumbnail_data['description'],
            'originalurl'       => $thumbnail_data['url'],
            'mime'              => $thumbnail_data['mime'],
            'type'              => $thumbnail_data['type'],
            'subtype'           => $thumbnail_data['subtype'],
            'filesize'          => $thumbnail_data['filesizeInBytes'],
            'readablefilesize'  => $thumbnail_data['filesizeHumanReadable'],
            'size'              => $size,
            'url'               => $selected_size['url'],
            'width'             => $selected_size['width'],
            'height'            => $selected_size['height'],
            'orientation'       => $selected_size['orientation'],
            'ratio'             => $selected_size['width'] / $selected_size['height']
        ];

        if (!empty($media_property_key) && isset($media[$media_property_key])) {
            return $media[$media_property_key];
        }

        return $media;
    }

}
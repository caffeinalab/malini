<?php

use Malini\Malini;

if (!function_exists('malini')) {
    function malini()
    {
        return Malini::getInstance();
    }
}

if (!function_exists('malini_add_accessor')) {
    function malini_add_accessor(string $name, string $namespace)
    {
        malini()->registerAccessor($name, $namespace);
    }
}

if (!function_exists('malini_add_decorator')) {
    function malini_add_decorator(string $name, string $namespace)
    {
        malini()->registerDecorator($name, $namespace);
    }
}

if (!function_exists('malini_post')) {
    function malini_post($post = null)
    {
        if (empty($post)) {
            $post = get_post();
        } elseif (is_int($post)) {
            $post = get_post($post);
        }

        return malini()->post($post);
    }
}

if (!function_exists('malini_archive')) {
    function malini_archive($posts = null)
    {
        if (is_null($posts)) {
            $posts = [];
            while (have_posts()) {
                the_post();
                $posts[] = get_post();
            }
        }

        return malini()->archive($posts);
    }
}

if (!function_exists('is_sequential_array')) {
    function is_sequential_array($value)
    {
        if (!is_array($value)) {
            return false;
        }
        if ([] === $value) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }
}

if (!function_exists('dump')) {
    function dump(...$args)
    {
        $message = implode(
            "\n\n",
            array_map(
                function ($value) {
                    return var_export($value, true);
                },
                $args
            )
        );
        $is_cli = in_array(php_sapi_name(), ['cli', 'cli-server']);
        if (!$is_cli) {
            $message = preg_replace(
                [
                    '/\&lt\;\!\-\-begin\-\-\&gt\;.+?\/\*end\*\//',
                    '/\/\*begin\*\/.+?\&lt\;\!\-\-end\-\-\&gt\;/',
                    '/array\&nbsp\;\(\<br\s\/\>\)/',
                ],
                [
                    '',
                    '',
                    'array ( )',
                ],
                highlight_string(
                    "<!--begin--><?php/*end*/\n"
                    .$message
                    ."\n/*begin*/?><!--end-->\n\n",
                    true
                )
            );
        }
        echo $message;
    }
}

if (!function_exists('dd')) {
    function dd(...$args)
    {
        dump(...$args);
        die();
    }
}

<?php

// Casts
if (!function_exists('string')) {
    function string($value) {
        return (string)$value;
    }
}

if (!function_exists('bool')) {
    function bool($value) {
        return (bool)$value;
    }
}

if (!function_exists('int')) {
    function int($value) {
        return (int)$value;
    }
}

if (!function_exists('float')) {
    function float($value) {
        return (float)$value;
    }
}

if (!function_exists('to_array')) {
    function to_array($value) {
        return (array)$value;
    }
}

if (!function_exists('to_object')) {
    function to_object($value) {
        return (object)$value;
    }
}

if (!function_exists('json')) {
    function json($value) {
        return json_encode($value, JSON_NUMERIC_CHECK);
    }
}

// String filters
if (!function_exists('lowercase')) {
    function lowercase(string $value) {
        return strtolower($value);
    }
}

if (!function_exists('uppercase')) {
    function uppercase(string $value) {
        return strtoupper($value);
    }
}

if (!function_exists('capitalize')) {
    function capitalize(string $value) {
        return ucwords($value);
    }
}

if (!function_exists('customcase')) {
    function customcase(string $value, string $delimiter) {
        $value = preg_replace('/\s+/u', '', ucwords($value));

        return lowercase(
            preg_replace(
                '/(.)(?=[A-Z])/u',
                '$1' . $delimiter,
                $value
            )
        );
    }
}

if (!function_exists('kebabcase')) {
    function kebabcase(string $value) {
        return customcase($value, '-');
    }
}

if (!function_exists('snakecase')) {
    function snakecase(string $value) {
        return customcase($value, '_');
    }
}

if (!function_exists('pascalcase')) {
    function pascalcase(string $value) {
        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace(
                    ['-', '_'],
                    ' ',
                    $value
                )
            )
        );
    }
}

if (!function_exists('camelcase')) {
    function camelcase(string $value) {
        return lcfirst(pascalcase($value));
    }
}

if (!function_exists('slug')) {
    function slug(string $value) {
        return sanitize_title($value);
    }
}

if (!function_exists('append')) {
    function append(string $value, string $suffix) {
        return $value . $suffix;
    }
}

if (!function_exists('prepend')) {
    function prepend(string $value, string $prefix) {
        return $prefix . $value;
    }
}

if (!function_exists('padleft')) {
    function padleft(string $value, int $pad_length, string $pad_string) {
        return str_pad($value, $pad_length, $pad_string, STR_PAD_LEFT);
    }
}

if (!function_exists('padright')) {
    function padright(string $value, int $pad_length, string $pad_string) {
        return str_pad($value, $pad_length, $pad_string, STR_PAD_RIGHT);
    }
}

if (!function_exists('padboth')) {
    function padboth(string $value, int $pad_length, string $pad_string) {
        return str_pad($value, $pad_length, $pad_string, STR_PAD_BOTH);
    }
}

if (!function_exists('hash')) {
    function hash(string $value) {
        return sha1($value);
    }
}

if (!function_exists('escape_html')) {
    function escape_html(string $value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('replace')) {
    function replace(string $value, string $what, string $with = '', $force_string_replacement = 0) {
        $force_string_replacement = (int)$force_string_replacement;
        // auto-detect if it's a preg_replace
        if (!$force_string_replacement
            && (
                strlen($what) > 1
                && strpos($what, '/') === 0
                && @preg_match($what, $value) !== false
            )) {
            return preg_replace($what, $with, $value);
        }
        return str_replace($what, $with, $value);
    }
}

if (!function_exists('truncate')) {
    function truncate(string $value, int $max_length, string $pad_with = '') {
        return strlen($value) <= $max_length
            ? $value
            : substr($value, 0, $max_length - strlen($pad_with)) . $pad_with;
    }
}

// Date filters
if (!function_exists('format_date')) {
    function format_date($value, string $format = 'Y-m-d') {
        return date($format, strtotime($value));
    }
}

// Array filters
if (!function_exists('skip')) {
    function skip(array $value, int $how_many) {
        return array_slice($value, $how_many);
    }
}

if (!function_exists('take')) {
    function take(array $value, int $how_many) {
        return array_slice($value, 0, $how_many);
    }
}

if (!function_exists('slice')) {
    function slice(array $value, int $from, int $how_many) {
        return array_slice($value, $from, $how_many);
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten(array $value) {
        $result = [];
        array_walk_recursive(
            $value,
            function($element, $index) use (&$result) {
                if (is_int($index)) {
                    $result[] = $element;
                } else {
                    $result[$index] = $element;
                }
            }
        );
        return $result;
    }
}

// Relation filters
if (!function_exists('load_post')) {
    function load_post($value) {
        return malini_post($value);
    }
}

if (!function_exists('load_posts')) {
    function load_posts(array $value) {
        return array_map('load_post', $value);
    }
}
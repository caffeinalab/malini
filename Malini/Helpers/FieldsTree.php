<?php

namespace Malini\Helpers;

class FieldsTree
{
    protected static function strParse(string $filter_field)
    {
        if (substr_count($filter_field, '(') !== substr_count($filter_field, ')')) {
            throw new \Exception(
                'Parentheses must be balanced; found '
                    .substr_count($filter_field, '(')
                    .' `(` and '
                    .substr_count($filter_field, ')')
                    .' `)`'
            );
        }
        if ($filter_field[0] == '(' && $filter_field[strlen($filter_field) - 1] == ')') {
            $filter_field = \substr(\substr($filter_field, 0, strlen($filter_field) - 1), 1);
        }
        $buffer = '';
        $stack = [];
        $depth = 0;
        $filter_length = strlen($filter_field);
        for ($i = 0; $i < $filter_length; ++$i) {
            $curr_char = $filter_field[$i];
            switch ($curr_char) {
                case '(':
                    $depth++;
                    break;
                case ')':
                    if ($depth > 0) {
                        --$depth;
                    } else {
                        throw new \Exception(
                            'Parentheses must be balanced; found a `)` without an equivalent `(`'
                        );
                    }
                    break;
                case ',':
                    if ($depth == 0) {
                        if ($buffer !== '') {
                            $stack[] = $buffer;
                            $buffer = '';
                        }
                        $curr_char = '';
                    }
                    break;
            }
            $buffer .= $curr_char;
        }
        if ($depth > 0) {
            throw new \Exception(
                'Parentheses must be balanced; found a `(` without an equivalent `)`'
            );
        }
        if ($buffer !== '') {
            $stack[] = $buffer;
        }

        return $stack;
    }

    public static function parse($filter_fields, array $opts = [])
    {
        $alias_separator = isset($opts['alias_separator'])
            ? $opts['alias_separator']
            : ':';

        if (is_string($filter_fields)) {
            $filter_fields = static::strParse($filter_fields);
        }
        $fields = [];
        foreach ($filter_fields as $key => $filter_field) {
            $callable = null;
            $children = [];
            $numeric_index = false;
            if (is_int($key) && is_string($filter_field) && strpos($filter_field, '.') > 0) {
                list($key, $filter_field) = explode('.', $filter_field, 2);
                if (ctype_digit((string) $key)) {
                    $key = (int) $key;
                    $filter_field = $filter_field;
                    $numeric_index = true;
                }
            }

            if ($key === '*') {
                $fields['*'] = static::parse($filter_field);
                continue;
            } elseif (is_int($key) && is_array($filter_field)) {
                if (isset($filter_field['name'])) {
                    $fields[] = [
                        'name' => $filter_field['name'],
                        'alias' => isset($filter_field['alias'])
                            ? $filter_field['alias']
                            : $filter_field['name'],
                        'filter' => null,
                        'children' => isset($filter_field['children'])
                            ? static::parse($filter_field['children'])
                            : [],
                    ];
                } else {
                    $fields['_'.$key] = static::parse($filter_field);
                }
                continue;
            } elseif (is_int($key) && $numeric_index) {
                $fields['_'.$key] = static::parse($filter_field);
                continue;
            } elseif (is_int($key)) {
                $name = $filter_field;
            } else {
                $name = $key;
                if ($filter_field instanceof \Closure) {
                    $callable = $filter_field;
                } else {
                    $children = static::parse($filter_field);
                }
            }

            $alias_separator_index = strpos($name, $alias_separator);
            if ($alias_separator_index === false) {
                $alias = $name;
            } else {
                $alias = \substr($name, 0, $alias_separator_index);
                $name = \substr($name, $alias_separator_index + 1);
            }

            $fields[] = [
                'name' => $name,
                'alias' => $alias,
                'filter' => $callable,
                'children' => $children,
            ];
        }

        return $fields;
    }

    public static function getIndexedFilter(int $index, array $filter_fields)
    {
        if (isset($filter_fields['_'.$index])) {
            return $filter_fields['_'.$index];
        } elseif (isset($filter_fields['*'])) {
            return $filter_fields['*'];
        }

        $check = str_replace(
            '_', '', implode('', array_keys($filter_fields))
        );

        return [];
        // throw new \Exception("Undefined indexed filter `{$index}`");
    }
}

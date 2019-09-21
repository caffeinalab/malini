<?php

namespace Malini\Helpers;

class JSON
{
    public static function print(string $template, \JsonSerializable $content, array $extra = []) {
        $result = new \StdClass;
        $result->template = $template;
        $result->data = $content->toObject();

        foreach ($extra as $key => $call) {
            if (is_string($call) && \method_exists($content, $call)) {
                $result->{$key} = $content->{$call}();
            } else if (is_callable($call)) {
                $result->{$key} = $call();
            } else {
                $result->{$key} = $call;
            }
        }

        $json = json_encode($result, JSON_NUMERIC_CHECK);
        header('Etag: "' . sha1($json) . '"');
        header('Content-type: application/json');
        echo $json;
    }
}
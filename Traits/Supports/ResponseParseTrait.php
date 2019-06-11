<?php

namespace Modules\Core\Traits\Supports;

use Illuminate\Support\Arr;

trait ResponseParseTrait
{
    protected static function parseMeta($data)
    {
        if ((is_array($data) && Arr::has($data, 'meta'))) {
            return Arr::get($data, 'meta');
        } else {
            return [];
        }
    }

    protected static function parseData($data)
    {
        if (is_array($data) && Arr::has($data, 'data')) {
            return Arr::get($data, 'data');
        } else {
            if (is_string($data) && json_decode($data)) {
                return json_decode($data);
            } else {
                return $data;
            }
        }
    }

    protected static function parseDataMeta($data)
    {
        if (is_array($data) && Arr::has($data, 'meta')) {
            return Arr::except($data, 'meta');
        } else {
            return $data;
        }
    }
}

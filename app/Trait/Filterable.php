<?php

namespace App\Trait;

use Illuminate\Support\Str;

trait Filterable
{
    public function scopeFilter($query, $filter)
    {
        $filter = is_array($filter) ? $filter : json_decode(urldecode($filter), true);
        foreach ($filter as $key => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }
            $op = $this->filterable_fields[$key] ?? '=';
            switch ($op) {
                case 'like':
                    $query->where("{$key}", 'like', "%$value%");
                    break;
                case '=':
                    $query->where("${key}", $value);
                    break;
            }
        }
    }
}

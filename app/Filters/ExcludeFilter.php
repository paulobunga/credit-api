<?php

namespace App\Filters;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ExcludeFilter implements Filter
{
    protected $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        $value = Arr::wrap($value);
        return $query->whereNotIn($this->field, $value);
    }
}

<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class JsonColumnFilter implements Filter
{
    protected $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        return $query->where($this->field, $value);
    }
}

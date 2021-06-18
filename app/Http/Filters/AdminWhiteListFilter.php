<?php

namespace App\Http\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AdminWhiteListFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        $query->whereHas('admin', function (Builder $q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
        })->orWhere('ip', 'like', "%{$value}%");
    }
}

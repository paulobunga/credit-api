<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DateFilter implements Filter
{
    protected $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        $timezone = env('APP_TIMEZONE');
        $user_timezone = auth()->user()->timezone ?? env('APP_TIMEZONE');

        switch ($property) {
            case 'created_at_between':
                return $query->whereRaw(
                    "CONVERT_TZ(
                        {$this->table}.created_at, 
                        '{$timezone}', 
                        '{$user_timezone}'
                    )
                    BETWEEN ? AND ?",
                    $value
                );
            case 'updated_at_between':
                return $query->whereRaw(
                    "CONVERT_TZ(
                        {$this->table}.updated_at, 
                        '{$timezone}', 
                        '{$user_timezone}')
                    BETWEEN ? AND ?",
                    $value
                );
            case 'date_between':
                return $query->whereRaw(
                    "CONVERT_TZ(
                        {$this->table}.start_at, 
                        '{$timezone}',
                        '{$user_timezone}') >= ? 
                    AND CONVERT_TZ(
                        {$this->table}.end_at,
                        '{$timezone}',
                        '{$user_timezone}') <= ?",
                    $value
                );
            default:
                return $query->where("{$this->table}.{$property}", $value);
        }
    }
}

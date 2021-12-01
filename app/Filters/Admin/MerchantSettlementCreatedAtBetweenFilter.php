<?php

namespace App\Filters\Admin;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use App\Filters\BaseTimezone;

class MerchantSettlementCreatedAtBetweenFilter implements Filter
{   
    protected $base_timezone;

    public function __construct() {
      $this->base_timezone = new BaseTimezone();
    }

    public function __invoke(Builder $query, $value, string $property)
    {
      $query->whereRaw("CONVERT_TZ(merchant_settlements.created_at, '{$this->base_timezone->db_timezone}', '{$this->base_timezone->user_timezone_offset}') BETWEEN ? AND ?", $value);
    }
}
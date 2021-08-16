<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class ReportController extends Controller
{
    public function reseller()
    {
        $reports = QueryBuilder::for(\App\Models\ReportDailyReseller::class)
            ->with([
                'reseller'
            ])
            ->leftjoin('resellers', 'resellers.id', '=', 'report_daily_resellers.reseller_id')
            ->select('report_daily_resellers.*', 'resellers.name AS name')
            ->allowedFilters([
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::callback(
                    'date_between',
                    fn ($query, $v) => $query->where([
                        ['report_daily_resellers.start_at', '>=', $v[0]],
                        ['report_daily_resellers.end_at', '<=', $v[1]],
                    ])
                ),
            ])
            ->allowedSorts([
                'id',
                'start_at',
                'end_at',
                'turnover',
                'credit',
                'coin',
                'amount',
                'currency',
                'name'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Admin\ReportResellerTransformer::class
        );
    }

    public function merchant()
    {
        $reports = QueryBuilder::for(\App\Models\ReportDailyMerchant::class)
            ->with([
                'merchant'
            ])
            ->leftjoin('merchants', 'merchants.id', '=', 'report_daily_merchants.merchant_id')
            ->select('report_daily_merchants.*', 'merchants.name AS name')
            ->allowedFilters([
                AllowedFilter::partial('name', 'merchants.name'),
                AllowedFilter::callback(
                    'date_between',
                    fn ($query, $v) => $query->where([
                        ['report_daily_merchants.start_at', '>=', $v[0]],
                        ['report_daily_merchants.end_at', '<=', $v[1]],
                    ])
                ),
            ])
            ->allowedSorts([
                'id',
                'name',
                'order_id',
                'amount',
            ])
            ->allowedSorts([
                'id',
                'start_at',
                'end_at',
                'turnover',
                'credit',
                'transaction_fee',
                AllowedSort::field('name', 'merchants.name'),
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Admin\ReportMerchantTransformer::class
        );
    }
}

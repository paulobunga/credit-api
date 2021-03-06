<?php

namespace App\Http\Controllers\Admin;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use App\Http\Controllers\Controller;
use App\Transformers\Admin\ReportResellerTransformer;
use App\Transformers\Admin\ReportMerchantTransformer;
use App\Filters\DateFilter;

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
                AllowedFilter::custom('date_between', new DateFilter('report_daily_resellers')),
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
            ]);

        return $this->paginate($reports, ReportResellerTransformer::class);
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
                AllowedFilter::custom('date_between', new DateFilter('report_daily_merchants')),
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
            ]);

        return $this->paginate($reports, ReportMerchantTransformer::class);
    }
}

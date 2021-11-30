<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use App\Transformers\Admin\ReportResellerTransformer;
use App\Transformers\Admin\ReportMerchantTransformer;
use App\Trait\UserTimezone;

class ReportController extends Controller
{ 
    use UserTimezone;

    protected $db_timezone;

    protected $user_timezone;

    public function __construct()
    {
        parent::__construct();
        $this->db_timezone = env('DB_TIMEZONE');
        $this->user_timezone = $this->userTimezoneOffset();
    }
    
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
                    fn ($query, $v) => $query->whereRaw("
                      CONVERT_TZ(report_daily_resellers.start_at, '{$this->db_timezone}', '{$this->user_timezone}') >= ? 
                      AND CONVERT_TZ(report_daily_resellers.end_at, '{$this->db_timezone}', '{$this->user_timezone}') <= ?
                    ", $v)
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
                AllowedFilter::callback(
                    'date_between',
                    fn ($query, $v) => $query->whereRaw("
                      CONVERT_TZ(report_daily_merchants.start_at, '{$this->db_timezone}', '{$this->user_timezone}') >= ? AND 
                      CONVERT_TZ(report_daily_merchants.end_at, '{$this->db_timezone}', '{$this->user_timezone}') <= ?
                    ", $v)
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
            ]);

        return $this->paginate($reports, ReportMerchantTransformer::class);
    }
}

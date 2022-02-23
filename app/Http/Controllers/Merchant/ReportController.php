<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = QueryBuilder::for(\App\Models\ReportDailyMerchant::class)
            ->allowedFilters([
                'currency',
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
                'start_at',
                'end_at',
                'turnover',
                'credit',
                'transaction_fee',
                'currency',
            ])
            ->where('merchant_id', Auth::id())
            ->paginate($this->perPage);

        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Merchant\ReportTransformer::class
        );
    }
}

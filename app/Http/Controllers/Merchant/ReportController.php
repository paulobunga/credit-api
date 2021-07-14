<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = QueryBuilder::for(
            \App\Models\ReportDailyMerchant::class::where('merchant_id', Auth::id())
        )
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Merchant\ReportTransformer::class
        );
    }

    public function month(Request $request)
    {
        $reports = QueryBuilder::for(
            \App\Models\ReportMonthlyMerchant::class::where('merchant_id', Auth::id())
                ->limit(6)
        )
            ->allowedFilters([])
            ->paginate($this->perPage);
        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Reseller\ReportMonthlyTransformer::class
        );
    }
}

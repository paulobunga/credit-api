<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ReportDailyReseller;
use App\Models\ReportMonthlyReseller;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = QueryBuilder::for(ReportDailyReseller::class)
            ->allowedFilters([
                AllowedFilter::callback(
                    'date_between',
                    fn ($query, $v) => $query->where([
                        ['start_at', '>=', $v[0]],
                        ['end_at', '<=', $v[1]],
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
            ])
            ->where('reseller_id', Auth::id());

        return $this->paginate(
            $reports,
            \App\Transformers\Reseller\ReportTransformer::class
        );
    }

    public function month(Request $request)
    {
        $reports = QueryBuilder::for(ReportMonthlyReseller::class)
            ->allowedFilters([])
            ->where('reseller_id', Auth::id())
            ->limit(6)
            ->paginate($this->perPage);

        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Reseller\ReportMonthlyTransformer::class
        );
    }
}

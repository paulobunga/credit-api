<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = QueryBuilder::for(
            \App\Models\ReportDailyReseller::class::where('reseller_id', Auth::id())
        )
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Reseller\ReportTransformer::class
        );
    }
}

<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ReportController extends Controller
{
    public function index()
    {
        $reports = QueryBuilder::for(\App\Models\ReportDailyMerchant::class)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Merchant\ReportTransformer::class
        );
    }
}

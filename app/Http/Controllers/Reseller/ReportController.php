<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ReportController extends Controller
{
    public function index()
    {
        $reports = QueryBuilder::for(\App\Models\ReportDailyReseller::class)
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

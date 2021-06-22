<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ReportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function reseller()
    {
        $reports = QueryBuilder::for(\App\Models\ReportResellerFund::class)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator(
            $reports,
            \App\Transformers\ReportResellerTransformer::class
        );
    }

    public function merchant()
    {
        $reports = QueryBuilder::for(\App\Models\ReportMerchantFund::class)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator(
            $reports,
            \App\Transformers\ReportMerchantTransformer::class
        );
    }
}

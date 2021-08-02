<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ReportController extends Controller
{
    public function reseller()
    {
        $reports = QueryBuilder::for(\App\Models\ReportDailyReseller::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
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
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator(
            $reports,
            \App\Transformers\Admin\ReportMerchantTransformer::class
        );
    }
}

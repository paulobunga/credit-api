<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class MerchantFundRecordController extends Controller
{
    protected $model = \App\Models\MerchantFundRecord::class;
    protected $transformer = \App\Transformers\Admin\MerchantFundRecordTransformer::class;

    public function index()
    {
        $merchant_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\MerchantFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_deposits, $this->transformer);
    }
}

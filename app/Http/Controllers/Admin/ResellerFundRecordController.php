<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerFundRecordController extends Controller
{
    protected $model = \App\Models\ResellerFundRecord::class;
    protected $transformer = \App\Transformers\Admin\ResellerFundRecordTransformer::class;

    public function index()
    {
        $reseller_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($reseller_deposits, $this->transformer);
    }
}

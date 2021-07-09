<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerWithdrawalController extends Controller
{
    protected $model = \App\Models\ResellerWithdrawal::class;
    protected $transformer = \App\Transformers\Admin\ResellerWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $reseller_withdrawals = QueryBuilder::for($this->model)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\resellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($reseller_withdrawals, $this->transformer);
    }
}

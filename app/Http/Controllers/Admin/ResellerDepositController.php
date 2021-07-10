<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ResellerDepositController extends Controller
{
    protected $model = \App\Models\ResellerDeposit::class;
    protected $transformer = \App\Transformers\Admin\ResellerDepositTransformer::class;

    public function index(Request $request)
    {
        $reseller_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                'name'
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($reseller_deposits, $this->transformer);
    }
}

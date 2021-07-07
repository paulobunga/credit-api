<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Merchant\DepositTransformer::class;

    public function index()
    {
        $deposits = QueryBuilder::for(
            $this->model::select('merchant_deposits.*')
                ->leftjoin('merchants', 'merchant_deposits.merchant_id', '=', 'merchants.id')
                ->where('merchant_deposits.merchant_id', Auth::id())
        )
            ->allowedFilters('name')
            ->paginate($this->perPage);

        return $this->response->withPaginator($deposits, $this->transformer);
    }
}

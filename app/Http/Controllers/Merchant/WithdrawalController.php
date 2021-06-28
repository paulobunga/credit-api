<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    protected $model = \App\Models\MerchantWithdrawal::class;
    protected $transformer = \App\Transformers\Merchant\WithdrawalTransformer::class;

    public function index()
    {
        $withdrawals = QueryBuilder::for($this->model::where('merchant_id', Auth::id()))
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\MerchantFilter),
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($withdrawals, $this->transformer);
    }
}

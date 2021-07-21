<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;

class DepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Merchant\DepositTransformer::class;

    public function index(Request $request)
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

    public function resend()
    {
        $deposit = $this->model::where([
            'id' => $this->parameters('deposit'),
            'merchant_id' => Auth::id()
        ])->firstOrFail();

        $deposit->update([
            'attempts' => 0,
            'callback_status' => 1,
        ]);

        // push deposit information callback to callback url
        Queue::push((new \App\Jobs\GuzzleJob(
            $deposit,
            new \App\Transformers\Api\DepositTransformer,
            $deposit->merchant->api_key
        )));

        return $this->response->item($deposit, $this->transformer);
    }
}

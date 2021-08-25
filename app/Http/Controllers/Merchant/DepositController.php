<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\MerchantDeposit;

use Spatie\QueryBuilder\AllowedFilter;

class DepositController extends Controller
{
    protected $model = MerchantDeposit::class;

    protected $transformer = \App\Transformers\Merchant\DepositTransformer::class;

    public function index(Request $request)
    {
        $deposits = QueryBuilder::for($this->model)
            ->join('reseller_bank_cards', 'merchant_deposits.reseller_bank_card_id', '=', 'reseller_bank_cards.id')
            ->join('payment_channels', 'reseller_bank_cards.payment_channel_id', '=', 'payment_channels.id')
            ->select('merchant_deposits.*', 'payment_channels.name AS channel')
            ->where('merchant_id', Auth::id())
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('merchant_order_id'),
                AllowedFilter::partial('channel', 'payment_channels.name'),
                AllowedFilter::partial('method'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('merchant_deposits.created_at', $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'merchant_order_id',
                'amount',
                'status',
                'callback_url',
                'attempts',
                'callback_status',
                'created_at',
            ])
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
            'callback_status' => MerchantDeposit::CALLBACK_STATUS['PENDING'],
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

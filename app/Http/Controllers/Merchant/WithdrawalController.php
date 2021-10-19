<?php

namespace App\Http\Controllers\Merchant;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Queue;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Models\MerchantWithdrawal;

use Spatie\QueryBuilder\AllowedFilter;

class WithdrawalController extends Controller
{
    protected $model = MerchantWithdrawal::class;

    protected $transformer = \App\Transformers\Merchant\WithdrawalTransformer::class;

    public function index(Request $request)
    {
        $deposits = QueryBuilder::for($this->model)
            ->join('payment_channels', 'merchant_withdrawals.payment_channel_id', '=', 'payment_channels.id')
            ->select('merchant_withdrawals.*', 'payment_channels.name AS channel')
            ->where('merchant_id', auth()->id())
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('merchant_order_id'),
                AllowedFilter::partial('channel', 'payment_channels.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('merchant_withdrawals.created_at', $v)
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
        $m = $this->model::where([
            'id' => $this->parameters('withdrawal'),
            'merchant_id' => auth()->id()
        ])->firstOrFail();

        $m->timestamps = false;
        $m->attempts = 0;
        $m->callback_status = $this->model::CALLBACK_STATUS['PENDING'];
        $m->save();

        // push deposit information callback to callback url
        Queue::push((new \App\Jobs\GuzzleJob(
            $m,
            new \App\Transformers\Api\WithdrawalTransformer,
            $m->merchant->api_key
        )));

        return $this->response->item($m, $this->transformer);
    }
}

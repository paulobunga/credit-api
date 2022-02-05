<?php

namespace App\Http\Controllers\Admin;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Queue;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use App\Models\MerchantDeposit;
use App\Http\Controllers\Controller;
use App\Filters\DateFilter;

class MerchantDepositController extends Controller
{
    protected $model = MerchantDeposit::class;

    protected $transformer = \App\Transformers\Admin\MerchantDepositTransformer::class;

    /**
     * Get Merchant Deposit lists
     * @param  \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $merchant_deposits = QueryBuilder::for($this->model)
            ->with(['merchant', 'reseller', 'paymentChannel'])
            ->join('merchants', 'merchants.id', '=', 'merchant_deposits.merchant_id')
            ->join('reseller_bank_cards', 'reseller_bank_cards.id', '=', 'merchant_deposits.reseller_bank_card_id')
            ->join('resellers', 'resellers.id', '=', 'reseller_bank_cards.reseller_id')
            ->join('payment_channels', 'payment_channels.id', '=', 'reseller_bank_cards.payment_channel_id')
            ->select(
                'merchant_deposits.*',
                'merchants.name AS merchant_name',
                'resellers.name AS reseller_name',
                'payment_channels.name AS channel'
            )
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('order_id'),
                AllowedFilter::partial('merchant_order_id'),
                AllowedFilter::partial('channel', 'payment_channels.name'),
                AllowedFilter::partial('merchant_name', 'merchants.name'),
                AllowedFilter::partial('reseller_name', 'resellers.name'),
                AllowedFilter::exact('status', 'merchant_deposits.status'),
                AllowedFilter::custom('created_at_between', new DateFilter('merchant_deposits')),
                AllowedFilter::custom('updated_at_between', new DateFilter('merchant_deposits')),
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'merchant_deposits.id'),
                AllowedSort::field('name', 'merchants.name'),
                'method',
                'currency',
                'callback_url',
                AllowedSort::field('channel', 'payment_channels.name'),
                AllowedSort::field('order_id'),
                AllowedSort::field('merchant_order_id'),
                AllowedSort::field('reseller_name', 'resellers.name'),
                AllowedSort::field('amount'),
                AllowedSort::field('status', 'merchant_deposits.status'),
                'attempts',
                'callback_status',
                AllowedSort::field('created_at', 'merchant_deposits.created_at'),
                'updated_at'
            ]);

        return $this->paginate($merchant_deposits, $this->transformer);
    }

    /**
     * Update merchant deposit by id
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method PUT
     * @return json
     */
    public function update(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('merchant_deposit'));
        if (
            !in_array($m->status, [
                MerchantDeposit::STATUS['PENDING'],
                MerchantDeposit::STATUS['EXPIRED'],
            ])
        ) {
            throw new \Exception('Status is not allowed to update', 401);
        }
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric|in:' . implode(',', [
                MerchantDeposit::STATUS['ENFORCED'],
                MerchantDeposit::STATUS['CANCELED'],
            ]),
            'reference_id' => 'required_if:status,' . MerchantDeposit::STATUS['ENFORCED'],
            'reason' => 'required_if:status,' . MerchantDeposit::STATUS['CANCELED'],
        ]);
        if ($request->status == MerchantDeposit::STATUS['ENFORCED']) {
            $m->update([
                'status' => $request->status,
                'extra' => [
                    'admin_id' => $request->admin_id,
                    'reference_id' => $request->reference_id
                ]
            ]);
        } else {
            $m->update([
                'status' => $request->status,
                'extra' => [
                    'admin_id' => $request->admin_id,
                    'reason' => $request->reason
                ]
            ]);
        }

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Resend callback to merchant
     * @method PUT
     * @return json
     */
    public function resend()
    {
        $m = $this->model::where([
            'id' => $this->parameters('merchant_deposit'),
        ])->firstOrFail();

        $m->timestamps = false;
        $m->attempts = 0;
        $m->callback_status = $this->model::CALLBACK_STATUS['PENDING'];
        $m->save();

        // push deposit information callback to callback url
        Queue::push((new \App\Jobs\GuzzleJob(
            $m,
            new \App\Transformers\Api\DepositTransformer(),
            $m->merchant->api_key
        )));

        return $this->response->item($m, $this->transformer);
    }
}

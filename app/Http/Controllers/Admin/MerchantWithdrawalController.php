<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\MerchantWithdrawal;
use App\Filters\Admin\MerchantWithdrawalCreatedAtBetweenFilter;
use App\Filters\Admin\MerchantWithdrawalUpdatedAtBetweenFilter;

class MerchantWithdrawalController extends Controller
{
    protected $model = MerchantWithdrawal::class;

    protected $transformer = \App\Transformers\Admin\MerchantWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $merchant_withdrawals = QueryBuilder::for($this->model)
            ->with(['merchant', 'reseller', 'paymentChannel'])
            ->join('merchants', 'merchants.id', '=', 'merchant_withdrawals.merchant_id')
            ->join('resellers', 'resellers.id', '=', 'merchant_withdrawals.reseller_id')
            ->join('payment_channels', 'payment_channels.id', '=', 'merchant_withdrawals.payment_channel_id')
            ->select(
                'merchant_withdrawals.*',
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
                AllowedFilter::exact('status', 'merchant_withdrawals.status'),
                AllowedFilter::custom('created_at_between', new MerchantWithdrawalCreatedAtBetweenFilter),
                AllowedFilter::custom('updated_at_between', new MerchantWithdrawalUpdatedAtBetweenFilter),
            ])
            ->allowedSorts([
                'id',
                'order_id',
                'merchant_order_id',
                'merchant_name',
                'reseller_name',
                'amount',
                'currency',
                'status',
                'created_at',
                'updated_at',
            ]);

        return $this->paginate($merchant_withdrawals, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant_withdrawal = $this->model::findOrFail($this->parameters('merchant_withdrawal'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric|in:' . implode(',', [
                MerchantWithdrawal::STATUS['APPROVED'],
                MerchantWithdrawal::STATUS['CANCELED'],
            ]),
            'reason' => 'required_if:status,' . MerchantWithdrawal::STATUS['CANCELED'],
        ]);
        if ($request->status == MerchantWithdrawal::STATUS['CANCELED']) {
            $merchant_withdrawal->update([
                'status' => $request->status,
                'extra' => [
                    'admin_id' => $request->admin_id,
                    'reason' => $request->reason
                ]
            ]);
        } else {
            $merchant_withdrawal->update([
                'status' => $request->status,
                'extra' => [
                    'admin_id' => $request->admin_id
                ]
            ]);
        }
        
        return $this->response->item($merchant_withdrawal, $this->transformer);
    }

    /**
     * Resend callback to merchant
     *
     * @return \Dingo\Api\Http\Response $response
     */
    public function resend()
    {
        $m = $this->model::where([
            'id' => $this->parameters('merchant_withdrawal'),
        ])->firstOrFail();

        $m->timestamps = false;
        $m->attempts = 0;
        $m->callback_status = $this->model::CALLBACK_STATUS['PENDING'];
        $m->save();

        // push deposit information callback to callback url
        Queue::push((new \App\Jobs\GuzzleJob(
            $m,
            new \App\Transformers\Api\DepositTransformer,
            $m->merchant->api_key
        )));

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Get slip url of withdrawal
     *
     * @method GET
     *
     * @return array
     */
    public function slip()
    {
        $withdrawal = $this->model::findOrFail($this->parameters('merchant_withdrawal'));
        if (!in_array($withdrawal->status, [
            MerchantWithdrawal::STATUS['FINISHED'],
            MerchantWithdrawal::STATUS['APPROVED'],
            MerchantWithdrawal::STATUS['CANCELED']
        ])) {
            throw new \Exception('Status is invalid', 401);
        }

        return response()->json([
            'message' => 'success',
            'data' => [
                'url' => $withdrawal->slipUrl
            ]
        ]);
    }
}

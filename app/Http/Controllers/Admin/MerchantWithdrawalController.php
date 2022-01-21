<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Queue;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\MerchantWithdrawal;
use App\Filters\DateFilter;

class MerchantWithdrawalController extends Controller
{
    protected $model = MerchantWithdrawal::class;

    protected $transformer = \App\Transformers\Admin\MerchantWithdrawalTransformer::class;

    /**
     * Get list of merchant withdrawals
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
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
                AllowedFilter::custom('created_at_between', new DateFilter('merchant_withdrawals')),
                AllowedFilter::custom('updated_at_between', new DateFilter('merchant_withdrawals')),
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

    /**
     * Update merchant withdrawal by id
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method PUT
     * @return void
     */
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
     * @method PUT
     * @return json
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
     * @return json
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

    /**
     * Transfer merchant withdrawal to another agent
     * @method PUT
     * @return json
     */
    public function transfer(Request $request)
    {
        $m = $this->model::where([
            'id' => $this->parameters('merchant_withdrawal'),
        ])->firstOrFail();
        $this->validate($request, [
            'reseller_id' => 'required|numeric',
        ]);
        $transfer_to = Reseller::findOrFail($request->reseller_id);
        if ($transfer_to->status != Reseller::STATUS['ACTIVE']) {
            throw new \Exception('Agent status is not active');
        }
        if (!$transfer_to->payout->status) {
            throw new \Exception('Agent payout status is not active');
        }
        $transfer_from = Reseller::findOrFail($m->reseller->id);
        $m->update([
            'reseller_id' => $request->reseller_id,
            'extra' => [
                'transfer_from_reseller_id' => $transfer_from->id,
                'transfer_by_admin_id' => auth()->id(),
            ],
        ]);
        // send notification
        $transfer_from->notify(new \App\Notifications\WithdrawalTransfer($m));
        $transfer_to->notify(new \App\Notifications\WithdrawalPending($m));

        return $this->response->item($m, $this->transformer);
    }
}

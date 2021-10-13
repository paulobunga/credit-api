<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\MerchantWithdrawal;

class MerchantWithdrawalController extends Controller
{
    protected $model = MerchantWithdrawal::class;

    protected $transformer = \App\Transformers\Admin\MerchantWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $merchant_withdrawals = QueryBuilder::for($this->model)
            ->with([
                'merchant'
            ])
            ->join('merchants', 'merchants.id', '=', 'merchant_withdrawals.merchant_id')
            ->select('merchant_withdrawals.*', 'merchants.name')
            ->allowedFilters([
                AllowedFilter::partial('name', 'merchants.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('merchant_withdrawals.created_at', $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'name',
                'order_id',
                'amount',
                'currency',
                'status'
            ]);

        return $this->paginate($merchant_withdrawals, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant_withdrawal = $this->model::findOrFail($this->parameters('merchant_withdrawal'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);
        $merchant_withdrawal->update([
            'status' => $request->status,
            'extra' => [
                'admin_id' => $request->admin_id
            ]
        ]);

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

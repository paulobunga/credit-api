<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;

class MerchantWithdrawalController extends Controller
{
    protected $model = \App\Models\MerchantWithdrawal::class;

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
        DB::beginTransaction();
        try {
            $merchant_withdrawal->update([
                'status' => $request->status,
                'info' => [
                    'admin_id' => $request->admin_id
                ]
            ]);
            if ($request->status == 1) {
                $transaction = $merchant_withdrawal->transactions()->create([
                    'transaction_method_id' => 2,
                    'amount' => $merchant_withdrawal->amount
                ]);
                $merchant_withdrawal->merchant->decrement('credit', $transaction->amount);
                $transaction = $merchant_withdrawal->transactions()->create([
                    'transaction_method_id' => 5,
                    'amount' => $merchant_withdrawal->amount * $merchant_withdrawal->merchant->transaction_fee
                ]);
                $merchant_withdrawal->merchant->decrement('credit', $transaction->amount);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return $this->response->item($merchant_withdrawal, $this->transformer);
    }
}

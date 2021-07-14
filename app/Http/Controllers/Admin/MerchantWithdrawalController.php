<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\DB;

class MerchantWithdrawalController extends Controller
{
    protected $model = \App\Models\MerchantWithdrawal::class;
    protected $transformer = \App\Transformers\Admin\MerchantWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $merchant_withdrawals = QueryBuilder::for($this->model)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\MerchantFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_withdrawals, $this->transformer);
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

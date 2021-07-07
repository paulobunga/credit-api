<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\DB;
use App\Models\TransactionMethod;

class MerchantDepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Admin\MerchantDepositTransformer::class;

    public function index()
    {
        $merchant_deposits = QueryBuilder::for($this->model)
            // ->allowedFilters([
            //     // AllowedFilter::custom('name', new \App\Http\Filters\MerchantFilter),
            // ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_deposits, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant_deposit = $this->model::findOrFail($this->parameters('merchant_deposit'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);
        $methods = TransactionMethod::all()->pluck('id', 'name');
        DB::beginTransaction();
        try {
            $merchant_deposit->update([
                'status' => $request->status,
                'info' => [
                    'admin_id' => $request->admin_id
                ]
            ]);
            // enforce
            if ($request->status == 4) {
                // reseller
                $transaction = $merchant_deposit->transactions()->create([
                    'transaction_method_id' => $methods['DEDUCT_CREDIT'],
                    'amount' => $merchant_deposit->amount
                ]);
                $merchant_deposit->reseller->decrement('credit', $transaction ->amount);
                // merchant
                $transaction = $merchant_deposit->transactions()->create([
                    'transaction_method_id' => $methods['TOPUP_CREDIT'],
                    'amount' => $transaction->amount
                ]);
                $transaction = $merchant_deposit->transactions()->create([
                    'transaction_method_id' => $methods['TRANSACTION_FEE'],
                    'amount' => $transaction->amount
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return $this->response->item($merchant_deposit, $this->transformer);
    }
}

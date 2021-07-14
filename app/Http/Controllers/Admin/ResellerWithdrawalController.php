<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\DB;

class ResellerWithdrawalController extends Controller
{
    protected $model = \App\Models\ResellerWithdrawal::class;
    protected $transformer = \App\Transformers\Admin\ResellerWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $reseller_withdrawals = QueryBuilder::for($this->model)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\resellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($reseller_withdrawals, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller_withdrawal = $this->model::findOrFail($this->parameters('reseller_withdrawal'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);
        DB::beginTransaction();
        try {
            $reseller_withdrawal->update([
                'status' => $request->status,
                'info' => [
                    'admin_id' => $request->admin_id
                ]
            ]);
            if ($request->status == 1) {
                $transaction = $reseller_withdrawal->transactions()->create([
                    'transaction_method_id' => 4,
                    'amount' => $reseller_withdrawal->amount
                ]);
                $reseller_withdrawal->reseller->decrement('coin', $transaction->amount);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return $this->response->item($reseller_withdrawal, $this->transformer);
    }
}

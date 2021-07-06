<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Reseller\DepositTransformer::class;

    public function index()
    {
        $deposits = QueryBuilder::for(
            $this->model::whereHas('reseller', function (Builder $query) {
                $query->where('resellers.id', Auth::id());
            })
                ->select('merchant_deposits.*')
                ->leftjoin(
                    'reseller_bank_cards',
                    'merchant_deposits.reseller_bank_card_id',
                    '=',
                    'reseller_bank_cards.id'
                )
                ->filter(request()->get('filter', '{}'))
                ->sort(request()->get('sort', 'id'))
        )
            ->allowedFilters('name')
            ->paginate($this->perPage);

        return $this->response->withPaginator($deposits, $this->transformer);
    }

    public function update(Request $request)
    {
        $deposit = $this->model::findOrFail($this->parameters('deposit'));
        if ($deposit->reseller->id != Auth::id()) {
            throw new \Exception('Unauthorize', 401);
        }
        $this->validate($request, [
            'status' => 'required|numeric',
        ]);
        DB::beginTransaction();
        try {
            $deposit->update([
                'status' => $request->status,
            ]);
            // approve
            if ($request->status == 2) {
                $transaction = $deposit->transactions()->create([
                    'transaction_method_id' => 1,
                    'amount' => $deposit->amount
                ]);
                $deposit->merchant->increment('credit', $transaction->amount);
                $transaction = $deposit->transactions()->create([
                    'transaction_method_id' => 5,
                    'amount' => $deposit->amount * $deposit->merchant->transaction_fee
                ]);
                $deposit->merchant->decrement('credit', $transaction->amount);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
        $deposit->merchant->notify(new \App\Notifications\DepositUpdateNotification($deposit));

        return $this->response->item($deposit, $this->transformer);
    }
}

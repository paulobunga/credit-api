<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\ResellerBankCard;
use App\Models\ResellerWithdrawal;
use App\DTO\ResellerWithdrawalExtra;

class SettlementController extends Controller
{
    protected $model = ResellerWithdrawal::class;

    protected $transformer = \App\Transformers\Reseller\SettlementTransformer::class;

    public function index(Request $request)
    {
        $withdrawals = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::callback(
                    'status',
                    function (Builder $query, $v) {
                        if (is_array($v)) {
                            $query->whereIn('status', $v);
                        } else {
                            $query->where('status', $v);
                        }
                    }
                ),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('created_at', $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'order_id',
                'amount',
                'status',
                'created_at',
            ])
            ->where('reseller_id', Auth::id())
            ->paginate($this->perPage);

        return $this->response->withPaginator($withdrawals, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'card' => 'required|numeric',
            'amount' => 'required|numeric|min:1',
        ]);
        ResellerBankCard::where([
            'id' => $request->card,
            'reseller_id' => auth()->id()
        ])->firstOrFail();
        $withdrawal = $this->model::create([
            'reseller_id' => auth()->id(),
            'reseller_bank_card_id' => $request->card,
            'type' => ResellerWithdrawal::TYPE['COIN'],
            'transaction_type' => Transaction::TYPE['RESELLER_WITHDRAW_COIN'],
            'amount' => $request->amount,
            'status' => ResellerWithdrawal::STATUS['PENDING'],
            'extra' => ['creator' => auth()->id()]
        ]);

        return $this->response->item($withdrawal, $this->transformer);
    }
}

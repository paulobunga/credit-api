<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\MerchantDeposit;
use App\Filters\DateFilter;

class DepositController extends Controller
{
    protected $model = MerchantDeposit::class;

    protected $transformer = \App\Transformers\Reseller\DepositTransformer::class;

    /**
     * Get Merchant Deposit lists
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $deposits = QueryBuilder::for($this->model)
            ->join(
                'reseller_bank_cards',
                'merchant_deposits.reseller_bank_card_id',
                '=',
                'reseller_bank_cards.id'
            )
            ->join(
                'payment_channels',
                'reseller_bank_cards.payment_channel_id',
                '=',
                'payment_channels.id'
            )
            ->whereHas('reseller', function (Builder $query) {
                $query->where('resellers.id', Auth::id());
            })
            ->select(
                'merchant_deposits.*',
                'payment_channels.name AS channel'
            )
            ->allowedFilters([
                AllowedFilter::partial('merchant_order_id'),
                AllowedFilter::partial('amount'),
                AllowedFilter::callback(
                    'status',
                    function (Builder $query, $v) {
                        if (is_array($v)) {
                            $query->whereIn('merchant_deposits.status', $v);
                        } else {
                            $query->where('merchant_deposits.status', $v);
                        }
                    }
                ),
                AllowedFilter::custom('created_at_between', new DateFilter('merchant_deposits')),
            ])
            ->allowedSorts([
                'id',
                'merchant_order_id',
                'channel',
                'amount',
                'status',
                'created_at',
            ]);

        return $this->paginate($deposits, $this->transformer);
    }

    public function update(Request $request)
    {
        $deposit = $this->model::findOrFail($this->parameters('deposit'));
        if ($deposit->reseller->id != Auth::id()) {
            throw new \Exception('Unauthorize', 401);
        }
        if (!in_array($deposit->status, [
            MerchantDeposit::STATUS['PENDING']
        ])) {
            throw new \Exception('Status is not allowed to update', 401);
        }
        $this->validate($request, [
            'status' => 'required|numeric|in:' . implode(',', [
                MerchantDeposit::STATUS['APPROVED'],
                MerchantDeposit::STATUS['REJECTED'],
            ]),
            'reference_id' => 'required_if:status,' . MerchantDeposit::STATUS['APPROVED'],
        ]);

        if ($request->status == MerchantDeposit::STATUS['APPROVED']) {
            $deposit->update([
                'status' => $request->status,
                'extra' => $deposit->extra + ['reference_id' => $request->reference_id]
            ]);
        } else {
            $deposit->update([
                'status' => $request->status,
            ]);
        }

        return $this->response->item($deposit, $this->transformer);
    }
}

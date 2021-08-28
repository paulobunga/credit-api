<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class DepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;

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
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('merchant_deposits.created_at', $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'merchant_order_id',
                'channel',
                'amount',
                'status',
                'created_at',
            ])
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

        $deposit->update([
            'status' => $request->status,
        ]);

        return $this->response->item($deposit, $this->transformer);
    }
}

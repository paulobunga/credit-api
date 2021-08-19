<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerBankCard;

class ResellerBankCardController extends Controller
{
    protected $model = ResellerBankCard::class;

    protected $transformer = \App\Transformers\Admin\ResellerBankCardTransformer::class;

    public function index(Request $request)
    {
        $reseller_bank_card = QueryBuilder::for($this->model)
            ->with([
                'reseller',
                'paymentChannel',
            ])
            ->join('resellers', 'resellers.id', '=', 'reseller_bank_cards.reseller_id')
            ->join('payment_channels', 'payment_channels.id', '=', 'reseller_bank_cards.payment_channel_id')
            ->select(
                'reseller_bank_cards.*',
                'resellers.name AS name',
                'payment_channels.name AS channel'
            )
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::partial('channel', 'payment_channels.name'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'channel',
                'status'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($reseller_bank_card, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller_bank_card = $this->model::findOrFail($this->parameters('reseller_bank_card'));
        $this->validate($request, [
            'attributes' => "required",
            'status' => 'required|in:' . implode(',', ResellerBankCard::STATUS),
        ]);
        // if (!in_array($request->bank_id, $reseller_bank_card->paymentChannel->banks->pluck('id')->toArray())) {
        //     throw new \Exception('Bank is not supported for current payment channel', 405);
        // }

        $reseller_bank_card->update([
            'status' => $request->status,
            'attributes' => $request->get('attributes')
        ]);

        return $this->response->item($reseller_bank_card, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $bank = $this->model::findOrFail($this->parameters('reseller_bank_card'));
        $bank->delete();

        return $this->success();
    }
}

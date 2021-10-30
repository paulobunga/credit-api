<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
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
                'payment_channels.name AS channel',
                'payment_channels.currency AS currency'
            )
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::partial('channel', 'payment_channels.name'),
                AllowedFilter::exact('currency', 'payment_channels.currency'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'channel',
                'currency',
                'status'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($reseller_bank_card, $this->transformer);
    }

        
    /**
     * update bank card of agent
     *
     * @param \Dingo\Api\Http\Request $request
     *
     * @return json
     */
    public function update(Request $request)
    {
        $reseller_bank_card = $this->model::findOrFail($this->parameters('reseller_bank_card'));
        $this->validate($request, [
            'attributes' => "required|array",
            'status' => 'required|in:' . implode(',', ResellerBankCard::STATUS),
        ]);
        $attributes = $reseller_bank_card->paymentChannel->validate($request->get('attributes'));
        ResellerBankCard::validateAttribute(
            $reseller_bank_card->paymentChannel->name,
            $reseller_bank_card->reseller->currency,
            $attributes,
            $reseller_bank_card->id
        );
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

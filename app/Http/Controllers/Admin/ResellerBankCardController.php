<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Reseller;
use App\Models\ResellerBankCard;

class ResellerBankCardController extends Controller
{
    protected $model = ResellerBankCard::class;

    protected $transformer = \App\Transformers\Admin\ResellerBankCardTransformer::class;

    /**
     * Get agent bank card list
     *
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
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
            ]);

        return $this->paginate($reseller_bank_card, $this->transformer);
    }

    /**
     * Create agent bank card
     *
     * @param \Dingo\Api\Http\Request $request
     * @method PUT
     * @return json
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'reseller' => 'required',
            'channel' => 'required',
            'attributes' => 'required|array',
            'status' => 'required|in:' . implode(',', ResellerBankCard::STATUS),
        ]);
        $reseller = Reseller::findOrFail($request->reseller);
        if ($reseller->level != Reseller::LEVEL['RESELLER']) {
            throw new \Exception('only agent are allowed to create bank card!');
        }
        $payment_channel = \App\Models\PaymentChannel::where('name', $request->channel)
            ->where('currency', $reseller->currency)
            ->firstOrFail();
        $attributes = $payment_channel->validate($request->get('attributes'));
        ResellerBankCard::validateAttribute($payment_channel, $attributes);
        $bankcard = $this->model::create([
            'reseller_id' => $reseller->id,
            'payment_channel_id' => $payment_channel->id,
            'attributes' => $attributes,
            'status' => $request->status,
        ]);

        return $this->response->item($bankcard, $this->transformer);
    }


    /**
     * update bank card of agent
     *
     * @param \Dingo\Api\Http\Request $request
     * @method PUT
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
            $reseller_bank_card->paymentChannel,
            $attributes,
            $reseller_bank_card->id
        );
        $reseller_bank_card->update([
            'status' => $request->status,
            'attributes' => $request->get('attributes')
        ]);

        return $this->response->item($reseller_bank_card, $this->transformer);
    }

    /**
     * Remove agent bankcard
     *
     * @param \Dingo\Api\Http\Request $request
     * @method DELETE
     * @return json
     */
    public function destroy(Request $request)
    {
        $bank = $this->model::findOrFail($this->parameters('reseller_bank_card'));
        $bank->delete();

        return $this->success();
    }
}

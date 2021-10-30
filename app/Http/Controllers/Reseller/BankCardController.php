<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerBankCard;
use App\Http\Controllers\Controller;

class BankCardController extends Controller
{
    protected $model = ResellerBankCard::class;

    protected $transformer = \App\Transformers\Reseller\BankCardTransformer::class;

    public function index(Request $request)
    {
        $bankcards = QueryBuilder::for($this->model)
            ->with([
                'paymentChannel',
            ])
            ->allowedFilters([
                'id',
                'name',
                AllowedFilter::exact('channel', 'payment_channel.name'),
                'status'
            ])
            ->where('reseller_id', Auth::id())
            ->paginate($this->perPage);

        return $this->response->withPaginator($bankcards, $this->transformer);
    }

    /**
     * agent create own bank card
     *
     * @param  \Dingo\Api\Http\RequestRequest $request
     *
     * @return json
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'channel' => 'required',
            'attributes' => 'required|array'
        ]);
        $currency = auth()->user()->currency;
        $payment_channel = \App\Models\PaymentChannel::where('name', $request->channel)
            ->where('currency', $currency)
            ->firstOrFail();
        $attributes = $payment_channel->validate($request->get('attributes'));
        ResellerBankCard::validateAttribute($request->channel, $currency, $attributes);
        $bankcard = $this->model::create([
            'reseller_id' => auth()->id(),
            'payment_channel_id' => $payment_channel->id,
            'attributes' => $attributes,
            'status' => ResellerBankCard::STATUS['INACTIVE']
        ]);

        return $this->response->item($bankcard, $this->transformer);
    }

    public function update(Request $request)
    {
        $bankcard = $this->model::with('paymentChannel')->where([
            'id' => $this->parameters('bankcard'),
            'reseller_id' => auth()->id()
        ])->firstOrFail();

        $this->validate($request, [
            'attributes' => 'required|array'
        ]);
        $attributes = $bankcard->paymentChannel->validate($request->get('attributes'));
        ResellerBankCard::validateAttribute(
            $bankcard->paymentChannel->name,
            auth()->user()->currency,
            $attributes,
            $bankcard->id
        );
        $bankcard->update([
            'attributes' => $attributes,
        ]);

        return $this->response->item($bankcard, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $bankcard = $this->model::where([
            'id' => $this->parameters('bankcard'),
            'reseller_id' => Auth::id()
        ])->firstOrFail();
        $bankcard->delete();

        return $this->success();
    }

    public function status(Request $request)
    {
        $bankcard = $this->model::where([
            'id' => $this->parameters('bankcard'),
            'reseller_id' => Auth::id()
        ])->firstOrFail();

        if (!in_array($bankcard->status, [
            ResellerBankCard::STATUS['ACTIVE'],
            ResellerBankCard::STATUS['DISABLED'],
        ])) {
            throw new \Exception('status is not allowd to modified!', 405);
        }

        $this->validate($request, [
            'status' => 'required|numeric|in:' . implode(',', [
                ResellerBankCard::STATUS['ACTIVE'],
                ResellerBankCard::STATUS['DISABLED'],
            ])
        ]);
        $bankcard->update([
            'status' => $request->status,
        ]);

        return $this->response->item($bankcard, $this->transformer);
    }
}

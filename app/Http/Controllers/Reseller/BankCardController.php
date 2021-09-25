<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerBankCard;

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

    public function store(Request $request)
    {
        $this->validate($request, [
            'channel' => 'required',
            'attributes' => 'required|array'
        ]);

        $payment_channel = \App\Models\PaymentChannel::where('name', $request->channel)
            ->where('currency', Auth::user()->currency)
            ->firstOrFail();
        $attributes = $payment_channel->validate($request->get('attributes'));
        $bankcard = $this->model::create([
            'reseller_id' => Auth::id(),
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
            'reseller_id' => Auth::id()
        ])->firstOrFail();

        $this->validate($request, [
            'attributes' => 'required|array'
        ]);
        $attributes = $bankcard->paymentChannel->validate($request->get('attributes'));
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

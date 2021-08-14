<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;

class BankCardController extends Controller
{
    protected $model = \App\Models\ResellerBankCard::class;

    protected $transformer = \App\Transformers\Reseller\BankCardTransformer::class;

    public function index(Request $request)
    {
        $bankcards = QueryBuilder::for($this->model)
            ->with([
                'bank',
                'paymentChannel',
            ])
            ->allowedFilters([
                'id',
                'name',
                AllowedFilter::exact('channel', 'payment_channel.name'),
            ])
            ->where('reseller_id', Auth::id())
            ->paginate($this->perPage);

        return $this->response->withPaginator($bankcards, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'bank_id' => "required|exists:banks,id",
            'channel' => 'required',
            'account_name' => 'required_if:type,online_bank',
            'account_no' => 'required',
            'status' => 'required|boolean',
        ]);
        $payment_channel = \App\Models\PaymentChannel::where('name', $request->channel)
            ->where('currency', Auth::user()->currency)->firstOrFail();
        $bankcard = $this->model::create([
            'reseller_id' => Auth::id(),
            'bank_id' => $request->bank_id,
            'payment_channel_id' => $payment_channel->id,
            'account_name' => $request->get('account_name', ''),
            'account_no' => $request->account_no,
            'status' => $request->status
        ]);

        return $this->response->item($bankcard, $this->transformer);
    }

    public function update(Request $request)
    {
        $bankcard = $this->model::where([
            'id' => $this->parameters('bankcard'),
            'reseller_id' => Auth::id()
        ])->firstOrFail();
        $this->validate($request, [
            'account_name' => 'required_if:type,online_bank',
            'account_no' => 'required',
            'status' => 'required|boolean',
        ]);

        $bankcard->update([
            'account_no' => $request->account_no,
            'account_name' => $request->account_name,
            'status' => $request->status
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
}

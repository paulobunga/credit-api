<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\Bank;

class BankCardController extends Controller
{
    protected $model = \App\Models\ResellerBankCard::class;
    protected $transformer = \App\Transformers\Reseller\BankCardTransformer::class;

    public function index(Request $request)
    {
        $banks = Bank::select('banks.*', 'payment_methods.name as type')
            ->leftjoin('payment_methods', 'banks.payment_method_id', '=', 'payment_methods.id');
        $bankcards = QueryBuilder::for(
            $this->model::where('reseller_id', Auth::id())
                ->select('reseller_bank_cards.*')
                ->joinSub($banks, 'banks', function ($join) {
                    $join->on('reseller_bank_cards.bank_id', '=', 'banks.id');
                })
                ->filter($request->get('filter', '{}'))
                ->sort($request->get('sort', 'id'))
        )
            ->allowedFilters([
                'id',
                'name',
                'banks.type',
                'banks.name'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($bankcards, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'bank_id' => "required|exists:banks,id",
            'type' => 'required',
            'account_name' => 'required_if:type,online_bank',
            'account_no' => 'required',
            'status' => 'required|boolean',
        ]);
        $payment_method = \App\Models\PaymentMethod::where('name', $request->type)->firstOrFail();
        $bankcard = $this->model::create([
            'reseller_id' => Auth::id(),
            'bank_id' => $request->bank_id,
            'payment_method_id' => $payment_method->id,
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

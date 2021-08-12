<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerBankCardController extends Controller
{
    protected $model = \App\Models\ResellerBankCard::class;

    protected $transformer = \App\Transformers\Admin\ResellerBankCardTransformer::class;

    public function index(Request $request)
    {
        $reseller_bank_card = QueryBuilder::for($this->model)
            ->with([
                'reseller',
                'bank',
                'paymentChannel',
            ])
            ->join('banks', 'banks.id', '=', 'reseller_bank_cards.bank_id')
            ->join('resellers', 'resellers.id', '=', 'reseller_bank_cards.reseller_id')
            ->join('payment_channels', 'payment_channels.id', '=', 'reseller_bank_cards.payment_channel_id')
            ->select(
                'reseller_bank_cards.*',
                'banks.name AS bank_name',
                'resellers.name AS reseller_name',
                'payment_channels.name AS channel'
            )
            ->allowedFilters([
                'id',
                'name',
                AllowedFilter::partial('channel'),
                AllowedFilter::partial('bank_name'),
                AllowedFilter::partial('reseller_name'),
            ])
            ->allowedSorts([
                'id',
                'reseller_name',
                'bank_name',
                'channel',
                'account_no',
                'account_name',
                'status'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($reseller_bank_card, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller_bank_card = $this->model::findOrFail($this->parameters('reseller_bank_card'));
        $this->validate($request, [
            'bank_id' => "required",
            'account_name' => 'required_if:type,online_bank',
            'account_no' => 'required',
        ]);
        if (!in_array($request->bank_id, $reseller_bank_card->paymentChannel->banks->pluck('id')->toArray())) {
            throw new \Exception('Bank is not supported for current payment channel', 405);
        }

        $reseller_bank_card->update([
            'bank_id' => $request->bank_id,
            'account_no' => $request->account_no,
            'account_name' => $request->account_name ?? '',
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

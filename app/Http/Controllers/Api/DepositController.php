<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Trait\SignValidator;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    use SignValidator;

    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Api\DepositTransformer::class;

    public function index()
    {
        $this->validateSign(request());
        $merchant_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                'merchant_order_id',
                'order_id'
                // AllowedFilter::custom('name', new \App\Http\Filters\MerchantFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_deposits, $this->transformer);
    }

    public function store(Request $request)
    {
        $merchant = $this->validateSign($request);
        $this->validate($request, [
            'merchant_order_id' => 'required',
            'amount' => 'numeric|min:10',
        ]);
        $reseller_bank_card = \App\Models\ResellerBankCard::where('status', true)->inRandomOrder()->firstOrFail();
        $merchant_deposit = $this->model::create([
            'merchant_id' => $merchant->id,
            'reseller_bank_card_id' => $reseller_bank_card->id,
            'merchant_order_id' => $request->merchant_order_id,
            'amount' => $request->amount,
            'status' => 0,
            'callback_url' => $merchant->callback_url,
            'reference_no' => ''
        ]);
        return $this->response->item($merchant_deposit, $this->transformer);
    }
}

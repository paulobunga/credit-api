<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Trait\SignValidator;
use Illuminate\Support\Str;
use App\Transformers\Api\DepositTransformer;
use Illuminate\Support\Facades\DB;

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
        return $this->response->withPaginator($merchant_deposits, new DepositTransformer);
    }

    public function store(Request $request)
    {
        $merchant = $this->validateSign($request);
        $this->validate($request, [
            'merchant_order_id' => 'required',
            'amount' => 'numeric|min:10',
        ]);
        DB::beginTransaction();
        try {
            $last_order = $this->model::lockForUpdate()->latest()->first();
            $reseller_bank_card = \App\Models\ResellerBankCard::where('status', true)->inRandomOrder()->firstOrFail();
            $merchant_deposit = $this->model::create([
                'merchant_id' => $merchant->id,
                'reseller_bank_card_id' => $reseller_bank_card->id,
                'order_id' => '#' . str_pad($last_order->id + 1, 8, "0", STR_PAD_LEFT) . time(),
                'merchant_order_id' => $request->merchant_order_id,
                'amount' => $request->amount,
                'status' => 0,
                'callback_url' => $merchant->callback_url,
                'reference_no' => ''
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
        $params = [
            'merchant_id' => $merchant->merchant_id,
            'merchant_order_id' => $merchant_deposit->merchant_order_id,
            'time' => time()
        ];
        $pay_url = app('Dingo\Api\Routing\UrlGenerator')->version(env('API_VERSION'))
            ->route('api.deposits.pay', $params + [
                'sign' => $this->createSign($params, $merchant->api_key)
            ]);

        return $this->response->item($merchant_deposit, new DepositTransformer(compact('pay_url')));
    }

    public function pay(Request $request)
    {
        $merchant = $this->validateSign($request);
        $this->validate($request, [
            'time' => 'required|numeric',
        ]);
        $deposit = $this->model::with(['merchant', 'resellerBankCard', 'bank'])->where([
            'merchant_id' => $merchant->id,
            'merchant_order_id' => $request->merchant_order_id,
        ])->firstOrFail();
        
        return view('pay', compact('deposit'));
    }
}

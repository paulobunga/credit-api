<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Trait\SignValidator;
use App\Transformers\Api\DepositTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class DepositController extends Controller
{
    use SignValidator;

    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Api\DepositTransformer::class;

    public function index()
    {
        $merchant = $this->validateSign(request());
        $merchant_deposits = QueryBuilder::for($this->model::where('merchant_id', $merchant->id))
            ->allowedFilters([
                'merchant_order_id',
                'order_id'
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_deposits, new DepositTransformer);
    }

    public function show(Request $request)
    {
        $merchant = $this->validateSign($request);
        $deposit = $this->model::where([
            'merchant_id' => $merchant->id,
            'merchant_order_id' => $this->parameters('deposit')
        ])->firstOrFail();

        return $this->response->item($deposit, new DepositTransformer);
    }

    public function store(Request $request)
    {
        $merchant = $this->validateSign($request);
        $this->validate($request, [
            'merchant_order_id' => [
                'required',
                Rule::unique('merchant_deposits')->where(function ($query) use ($request, $merchant) {
                    return $query->where([
                        'merchant_id' => $merchant->id,
                        'merchant_order_id' => $request->merchant_order_id
                    ]);
                }),
            ],
            'account_no' => 'required',
            'account_name' => 'required_if:type,online_bank',
            'type' => 'required',
            'amount' => 'numeric|min:10',
        ]);
     
        $reseller_bank_card = \App\Models\ResellerBankCard::whereHas(
            'paymentMethod',
            function (Builder $query) use ($request) {
                $query->where('payment_methods.name', strtolower($request->type));
            }
        )
            ->where('status', true)->inRandomOrder()->firstOrFail();
        DB::beginTransaction();
        try {
            $last_order = $this->model::lockForUpdate()->latest()->first();
            $merchant_deposit = $this->model::create([
                'merchant_id' => $merchant->id,
                'reseller_bank_card_id' => $reseller_bank_card->id,
                'order_id' => '#' . str_pad($last_order->id + 1, 8, "0", STR_PAD_LEFT) . time(),
                'merchant_order_id' => $request->merchant_order_id,
                'account_no' => $request->account_no,
                'account_name' => $request->get('account_name', ''),
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
            'uuid' => $merchant->uuid,
            'merchant_order_id' => $merchant_deposit->merchant_order_id,
            'time' => time()
        ];
        $pay_url = app('Dingo\Api\Routing\UrlGenerator')->version(env('API_VERSION'))
            ->route('api.deposits.pay', $params + [
                'sign' => $this->createSign($params, $merchant->api_key)
            ]);

        return $this->response->item($merchant_deposit, new DepositTransformer(compact('pay_url')));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'reference_no' => "required",
            'merchant_id' => 'required',
        ]);
        $deposit = $this->model::where([
            'merchant_id' => $request->merchant_id,
            'merchant_order_id' => $this->parameters('deposit')
            ])->firstOrFail();
        if ($deposit->status != 0) {
            throw new \Exception('deposit is already pending', 510);
        }
        $deposit->update([
            'status' => 1,
            'reference_no' => $request->reference_no
        ]);
        $deposit->reseller->notify(new \App\Notifications\DepositPendingNotification($deposit));

        return $this->success();
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

        return view($deposit->paymentMethod->name, compact('deposit'));
    }
}

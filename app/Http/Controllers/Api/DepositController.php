<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Trait\SignValidator;
use App\Transformers\Api\DepositTransformer;
use \App\Models\MerchantDeposit;
use App\Models\PaymentChannel;
use App\Models\ResellerBankCard;

class DepositController extends Controller
{
    use SignValidator;

    protected $model = MerchantDeposit::class;
    protected $transformer = DepositTransformer::class;

    public function index(Request $request)
    {
        $merchant = $this->validateSign($request);
        $merchant_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                'merchant_order_id',
                'order_id'
            ])
            ->where('merchant_id', $merchant->id)
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
        $cs = app(\App\Settings\CurrencySetting::class);
        $this->validate($request, [
            'merchant_order_id' => [
                'required',
                Rule::unique('merchant_deposits')->where(function ($query) use ($merchant) {
                    return $query->where('merchant_id', $merchant->id);
                }),
            ],
            'currency' => 'required|in:' . implode(',', $cs->types),
            'channel' => 'required',
            'method' => 'required',
            'amount' => 'required|numeric|min:1',
            'callback_url' => 'nullable|url'
        ]);
        $channel = PaymentChannel::where([
            'status' => true,
            'currency' => $request->currency,
            'name' => $request->channel
        ])->firstOrFail();

        if (!in_array($request->method, $channel->paymentMethods)) {
            throw new \Exception('Method is not supported!', 405);
        }
        // $attributes = $channel->validate($request->all());

        $sql = "SELECT 
                    rbc.id,
                    pc.name AS channel,
                    pc.currency AS currency,
                    rbc.reseller_id,
                    rbc.attributes,
                    r.pending_limit,
                    SUM(
                        CASE
                            WHEN md.amount = :amount THEN r.pending_limit
                            WHEN md.amount > 0 THEN 1
                            ELSE 0
                        END
                    ) AS pending
                FROM payment_channels AS pc
                INNER JOIN reseller_bank_cards AS rbc ON pc.id = rbc.payment_channel_id
                LEFT JOIN resellers AS r ON rbc.reseller_id = r.id
                LEFT JOIN merchant_deposits AS md ON rbc.id = md.reseller_bank_card_id 
                    AND md.status <= :merchant_deposit_status
                WHERE pc.currency = :currency 
                    AND pc.name = :channel 
                    AND pc.status = :channel_status
                    AND rbc.status = :card_status
                    AND r.credit >= :credit
                GROUP BY rbc.id
                ORDER BY pending ASC
                ";

        $rows = DB::select($sql, [
            'currency' => $request->currency,
            'credit' => $request->amount,
            'amount' => $request->amount,
            'channel' => $request->channel,
            'channel_status' => 1,
            'merchant_deposit_status' => MerchantDeposit::STATUS['PENDING'],
            'card_status' => ResellerBankCard::STATUS['ACTIVE'],
        ]);
        // dd($rows);
        foreach ($rows as $row) {
            if ($row->pending >= $row->pending_limit) {
                continue;
            } else {
                $reseller_bank_card = $row;
                break;
            }
        }
        if (!isset($reseller_bank_card)) {
            throw new \Exception('BankCard are unavailable!', 404);
        }
        DB::beginTransaction();
        try {
            $merchant_deposit = $this->model::create([
                'merchant_id' => $merchant->id,
                'reseller_id' => $reseller_bank_card->reseller_id,
                'reseller_bank_card_id' => $reseller_bank_card->id,
                'merchant_order_id' => $request->merchant_order_id,
                'method' => $request->method,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => MerchantDeposit::STATUS['PENDING'],
                'callback_url' => $request->get('callback_url', $merchant->callback_url),
                'account_no' => '',
                'account_name' => '',
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
        if ($deposit->status != MerchantDeposit::STATUS['CREATED']) {
            throw new \Exception('deposit is already pending', 510);
        }
        $deposit->update([
            'status' => MerchantDeposit::STATUS['PENDING'],
            'reference_no' => $request->reference_no
        ]);

        return $this->success();
    }

    public function pay(Request $request)
    {
        $merchant = $this->validateSign($request);
        $this->validate($request, [
            'time' => 'required|numeric',
        ]);
        $deposit = $this->model::with(['merchant', 'resellerBankCard', 'paymentChannel'])->where([
            'merchant_id' => $merchant->id,
            'merchant_order_id' => $request->merchant_order_id,
        ])->firstOrFail();
        $channel = $deposit->paymentChannel;

        return view(strtolower($deposit->method), [
            'deposit' => $deposit,
            'channel' => $channel,
            'subview' => strtolower("{$deposit->method}s.{$channel->name}.{$channel->currency}"),
            'attributes' => $deposit->resellerBankCard->attributes
        ]);
    }
}

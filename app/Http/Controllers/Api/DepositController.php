<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Trait\SignValidator;
use App\Models\Reseller;
use App\Models\MerchantDeposit;
use App\Models\PaymentChannel;
use App\Models\ResellerBankCard;
use App\Transformers\Api\DepositTransformer;

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
            'currency' => 'required|in:' . implode(',', array_keys($cs->currency)),
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

        $sql = "WITH reseller_channels AS (
            SELECT
                r.id AS reseller_id,
                rbc.id AS reseller_bank_card_id,
                pc.NAME AS channel,
                r.currency AS currency,
                SUM(
                    CASE
                        WHEN md.status <= :md_status THEN 1
                        ELSE 0 
                    END 
                ) AS pending,
                SUM(
                    CASE
                        WHEN md.status <= :same_md_status AND md.amount = {$request->amount} THEN 1
                        ELSE 0 
                    END 
                ) AS same_amount,
                r.pending_limit AS pending_limit 
            FROM
                reseller_bank_cards AS rbc
                LEFT JOIN resellers AS r ON rbc.reseller_id = r.id
                LEFT JOIN merchant_deposits AS md ON md.reseller_bank_card_id = rbc.id
                LEFT JOIN payment_channels AS pc ON rbc.payment_channel_id = pc.id 
            WHERE
                r.currency = '{$request->currency}'
                AND r.credit >= {$request->amount}
                AND r.STATUS = :r_status
                AND rbc.STATUS = :rbc_status
                AND pc.STATUS = :pc_status
                AND pc.currency = '{$request->currency}'
                GROUP BY rbc.id
            ),
            reseller_pending AS (
            SELECT
                reseller_id,
                SUM(pending) AS total_pending 
                FROM
                    reseller_channels
                GROUP BY
                    reseller_id 
                ) 
            SELECT
                * 
            FROM
                reseller_channels
                JOIN reseller_pending USING ( reseller_id ) 
            WHERE total_pending < pending_limit 
                AND channel = '{$request->channel}'
                AND same_amount = 0";

        $reseller_bank_cards = DB::select($sql, [
            'r_status' => Reseller::STATUS['ACTIVE'],
            'pc_status' => PaymentChannel::STATUS['ACTIVE'],
            'md_status' => MerchantDeposit::STATUS['PENDING'],
            'same_md_status' => MerchantDeposit::STATUS['PENDING'],
            'rbc_status' => ResellerBankCard::STATUS['ACTIVE'],
        ]);
        // dd($reseller_bank_cards);
        if (empty($reseller_bank_cards)) {
            throw new \Exception('BankCard are unavailable!', 404);
        }
        $reseller_bank_card = Arr::random($reseller_bank_cards);
        // dd($reseller_bank_card);
        DB::beginTransaction();
        try {
            $merchant_deposit = $this->model::create([
                'merchant_id' => $merchant->id,
                'reseller_id' => $reseller_bank_card->reseller_id,
                'reseller_bank_card_id' => $reseller_bank_card->reseller_bank_card_id,
                'merchant_order_id' => $request->merchant_order_id,
                'method' => $request->method,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => MerchantDeposit::STATUS['PENDING'],
                'callback_url' => $request->get('callback_url', $merchant->callback_url),
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
            'extra' => array_merge($request->extra, [
                'reference_no' => $request->reference_no
            ])
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

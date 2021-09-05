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

/**
 * @group Deposit API
 *
 * Before using API, make sure you have an merchant acount.
 *
 *
 */
class DepositController extends Controller
{
    use SignValidator;

    protected $model = MerchantDeposit::class;

    protected $transformer = DepositTransformer::class;

    /**
     * Get deposit list
     *
     * This endpoint lets you get deposit list.
     * <aside class="notice">
     * <strong>Deposit Status</strong>
     * <ol>
     * <li>PENDING</li>
     * <li>APPROVED</li>
     * <li>REJECTED</li>
     * <li>ENFORCED</li>
     * <li>CANCELED</li>
     * </ol>
     * </aside>
     *
     * @authenticated
     * @queryParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @queryParam per_page number Page count. Default: 10, Maximum: 100. Example: 1
     * @queryParam page number Set Page Number. Default: 1. Example: 1
     * @queryParam filter[status] number Filter status of deposit. Example: 1
     * @queryParam sign string required Signature. Example: 44ab5404efb22f3e3b28fec1c29f2eae
     * @transformerCollection App\Transformers\Api\DepositTransformer
     * @transformerModel App\Models\MerchantDeposit
     * @transformerPaginator League\Fractal\Pagination\IlluminatePaginatorAdapter 10
     * @response status=200 scenario="empty record"
     * {
     *      "data": [],
     *      "meta": {
     *          "pagination": {
     *              "total": 0,
     *              "count": 0,
     *              "per_page": 10,
     *              "current_page": 1,
     *              "total_pages": 1,
     *              "links": {},
     *              "sortBy": "id",
     *              "descending": false
     *          }
     *      }
     * }
     *
     */
    public function index(Request $request)
    {
        $merchant = $this->validateSign($request);
        $merchant_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                'merchant_order_id',
                'status'
            ])
            ->where('merchant_id', $merchant->id)
            ->paginate($this->perPage);

        return $this->response->withPaginator($merchant_deposits, new DepositTransformer);
    }

    /**
     * Get a deposit
     *
     * This endpoint lets you get a deposit.
     *
     * @authenticated
     * @urlParam id required The Merchant Order ID of the deposit. Example: 9798223690986
     * @queryParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @queryParam sign string required Signature. Example: e38c3a02a3d9757c912d0dc6240a5c88
     * @transformer App\Transformers\Api\DepositTransformer
     * @transformerModel App\Models\MerchantDeposit
     * @response status=404 scenario="not found"
     * {"message": "No query results for model [App\\Models\\MerchantDeposit]."}
     */
    public function show(Request $request)
    {
        $merchant = $this->validateSign($request);
        $deposit = $this->model::where([
            'merchant_id' => $merchant->id,
            'merchant_order_id' => $this->parameters('deposit')
        ])->firstOrFail();

        return $this->response->item($deposit, new DepositTransformer);
    }

    /**
     * Create a deposit
     *
     * This endpoint lets you create a deposit.
     *
     * @authenticated
     * @bodyParam merchant_order_id string required The order id created by merchant. Example: 97982236909861
     * @bodyParam currency string required The currency of the deposit. Example: VND
     * @bodyParam channel string required Payment Channel of the deposit. Example: MOMOPAY
     * @bodyParam method string required Payment method supported by selected Payment channel.
     * Example: QRCODE
     * @bodyParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @bodyParam sign string required Signature. Example: c8104a183967516bbb542d10dcc04f2e
     * @bodyParam amount string required Amount of the deposit. Example: 1000
     * @bodyParam callback_url url Callback URL of the deposit,
     * if not set, it would be the setting in merchant panel.
     * Example: http://callback.url/0001
     * @transformer App\Transformers\Api\DepositTransformer 
     * {"pay_url":":base_url/pay/deposits?uuid=224d4a1f-6fc5-4039-bd81-fcbc7f88c659&merchant_order_id=97982236909861&time=1630476187&sign=57cb165e201dea3a4084e0f97eeda637"}
     * @transformerModel App\Models\MerchantDeposit
     * @response status=422 scenario="parameter error"
     * {
     *      "message": "The merchant order id has already been taken.",
     *      "errors": {
     *          "merchant_order_id": [
     *              "The merchant order id has already been taken."
     *          ]
     *      }
     * }
     */
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

        return $this->response->item($merchant_deposit, new DepositTransformer([
            'pay_url' => $merchant_deposit->payUrl
        ]));
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

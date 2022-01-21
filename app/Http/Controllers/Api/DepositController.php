<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Trait\SignValidator;
use App\Models\Reseller;
use App\Models\MerchantDeposit;
use App\Models\PaymentChannel;
use App\Models\ResellerBankCard;
use App\DTO\ResellerPayIn;
use App\Transformers\Api\DepositTransformer;

/**
 * @group Deposit API
 *
 * Before using API, make sure you have an merchant acount.
 * <h3>Status</h3>
 * <table>
 * <thead>
 * <tr>
 * <td>Created</td>
 * <td>Pending</td>
 * <td>Approved</td>
 * <td>Rejected</td>
 * <td>Enforced</td>
 * <td>Canceled</td>
 * </tr>
 * </thead>
 * <tr>
 * <td>0</td>
 * <td>1</td>
 * <td>2</td>
 * <td>3</td>
 * <td>4</td>
 * <td>5</td>
 * </tr>
 * </table>
 * <h3>Callback Status</h3>
 * <table>
 * <thead>
 * <tr>
 * <td>Created</td>
 * <td>Pending</td>
 * <td>Finished</td>
 * <td>Failed</td>
 * </tr>
 * </thead>
 * <tr>
 * <td>0</td>
 * <td>1</td>
 * <td>2</td>
 * <td>3</td>
 * </tr>
 * </table>
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
     *
     * @authenticated
     * @queryParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @queryParam per_page number Page count. Default: 10, Maximum: 100. Example: 1
     * @queryParam page number Set Page Number. Default: 1. Example: 1
     * @queryParam filter[status] number Filter status of deposit. Example: 1
     * @queryParam sign string required Signature. Example: 44ab5404efb22f3e3b28fec1c29f2eae
     * @response status=200
     * {
     *   "data": {
     *     "name": "Test Merchant",
     *     "order_id": "DAaR2@WdEtinadjy",
     *     "merchant_order_id": "9798223690986",
     *     "player_id": "1",
     *     "amount": "500.0000",
     *     "currency": "INR",
     *     "status": 1,
     *     "callback_url": ":base_url/demos/callback/9798223690986"
     *   },
     *   "meta": {
     *     "pagination": {
     *       "total": 1,
     *       "count": 1,
     *       "per_page": 10,
     *       "current_page": 1,
     *       "total_pages": 1,
     *       "links": {},
     *       "sortBy": "id",
     *       "descending": false
     *     }
     *   }
     * }
     * @response status=200 scenario="empty record"
     * {
     *    "data": [],
     *    "meta": {
     *        "pagination": {
     *            "total": 0,
     *            "count": 0,
     *            "per_page": 10,
     *            "current_page": 1,
     *            "total_pages": 1,
     *            "links": {},
     *            "sortBy": "id",
     *            "descending": false
     *        }
     *    }
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
     * @response status=200
     * {
     *   "data": {
     *     "name": "Test Merchant",
     *     "order_id": "DAaR2@WdEtinadjy",
     *     "merchant_order_id": "9798223690986",
     *     "player_id": "1",
     *     "amount": "500.0000",
     *     "currency": "INR",
     *     "status": 1,
     *     "callback_url": ":base_url/demos/callback/9798223690986"
     *   }
     * }
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
     * @bodyParam merchant_order_id string required The order id created by merchant. Example: 9798223690986
     * @bodyParam player_id string required Unique id of player. Example: 1
     * @bodyParam currency string required The currency of the deposit. Example: INR
     * @bodyParam channel string required Payment Channel of the deposit. Example: UPI
     * @bodyParam method string required Payment method supported by selected Payment channel.
     * Example: QRCODE
     * @bodyParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @bodyParam sign string required Signature. Example: c8104a183967516bbb542d10dcc04f2e
     * @bodyParam amount string required Amount of the deposit. Example: 500
     * @bodyParam callback_url url required Callback URL of the deposit, Example: :base_url/demos/callback/9798223690986
     * @response status=200
     * {
     *   "data": {
     *     "name": "Test Merchant",
     *     "order_id": "DAaR2@WdEtinadjy",
     *     "merchant_order_id": "9798223690986",
     *     "player_id": "1",
     *     "amount": "500.0000",
     *     "currency": "INR",
     *     "status": 1,
     *     "callback_url": ":base_url/demos/callback/9798223690986",
     *     "pay_url": ":base_url/pay/deposits?uuid=224d4a1f-6fc5-4039-bd81-fcbc7f88c659&merchant_order_id=9798223690986&time=1633170173&sign=b4bf486861328f5a5dd43afe83958fdf"
     *   }
     * }
     * @response status=422 scenario="parameter error"
     * {
     *      "message": "The merchant order id has already been taken.",
     *      "errors": {
     *          "merchant_order_id": [
     *              "The merchant order id has already been taken."
     *          ]
     *      }
     * }
     * @callback status=200
     * // Callback request format
     * // Verify the signature with the request data
     * {
     *     "name": "Test Merchant",
     *     "order_id": "DAaR2@WdEtinadjy",
     *     "merchant_order_id": "9798223690986",
     *     "amount": "500.0000",
     *     "status": 2,
     *     "callback_url": ":base_url/demos/callback/9798223690986",
     *     "sign": "09508cdf7f1d089e108d462d182204e6"
     * }
     * @callback_response status=200
     * // Send the following response format
     * // Otherwise system will keep sending callback request
     * // every 30 seconds, until
     * // reach the failure limitation
     * {
     *     "message": "ok",
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
            'amount' => 'required|numeric',
            'callback_url' => 'required|url'
        ]);

        $same_orders = $this->model::where([
            'merchant_id' => $merchant->id,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'player_id' => $request->get('player_id', -1)
        ])->whereIn('status', [
            MerchantDeposit::STATUS['PENDING'],
            MerchantDeposit::STATUS['EXPIRED'],
        ])->whereBetween('created_at', [
            Carbon::now()->subHours(24),
            Carbon::now(),
        ])->count();

        if ($same_orders) {
            throw new \Exception('Same amount payin order is found in 24 hours!', 405);
        }

        $channel = PaymentChannel::where([
            'payin->status' => true,
            'currency' => $request->currency,
            'name' => $request->channel
        ])->firstOrFail();

        if (!in_array($request->method, $channel->paymentMethods)) {
            throw new \Exception('Method is not supported!', 405);
        }

        if ($request->amount < $channel->payin->min || $request->amount > $channel->payin->max) {
            throw new \Exception(
                "Amount is not in range[{$channel->payin->min}, {$channel->payin->max}]!",
                405
            );
        }

        $sql = "WITH reseller_channels AS (
            SELECT
                r.id AS reseller_id,
                rbc.id AS reseller_bank_card_id,
                pc.NAME AS channel,
                r.credit AS credit,
                r.currency AS currency,
                COUNT(md.id) AS pending,
                COALESCE(SUM(md.amount),0) AS pending_amount,
                SUM(
                    CASE
                        WHEN md.amount = {$request->amount} THEN 1
                        ELSE 0 
                    END 
                ) AS same_amount,
                r.payin->>'$.pending_limit' AS pending_limit 
            FROM
                reseller_bank_cards AS rbc
                LEFT JOIN resellers AS r ON rbc.reseller_id = r.id
                LEFT JOIN payment_channels AS pc ON rbc.payment_channel_id = pc.id
                LEFT JOIN merchant_deposits AS md ON md.reseller_bank_card_id = rbc.id AND md.status <= :md_status 
            WHERE
                r.currency = '{$request->currency}'
                AND r.credit >= {$request->amount}
                AND r.LEVEL = :r_level
                AND r.STATUS = :r_status
                AND r.payin->>'$.status' = :r_payin_status
                AND rbc.STATUS = :rbc_status
                AND pc.payin->>'$.status' = :pc_status
                AND pc.currency = '{$request->currency}'
                GROUP BY rbc.id
            ),
            reseller_pending AS (
            SELECT
                reseller_id,
                SUM(pending_amount) AS total_pending_amount,
                SUM(pending) AS total_pending,
                SUM(same_amount) AS total_same_amount
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
                AND total_pending_amount + {$request->amount} <= credit
                AND channel = '{$request->channel}' 
                AND total_same_amount = 0";
        // dd($sql);
        $reseller_bank_cards = DB::select($sql, [
            'r_status' => Reseller::STATUS['ACTIVE'],
            'r_payin_status' => ResellerPayIn::STATUS['ACTIVE'],
            'r_level' => Reseller::LEVEL['RESELLER'],
            'pc_status' => PaymentChannel::STATUS['ACTIVE'],
            'md_status' => MerchantDeposit::STATUS['PENDING'],
            'rbc_status' => ResellerBankCard::STATUS['ACTIVE'],
        ]);
        // dd($reseller_bank_cards);
        if (empty($reseller_bank_cards)) {
            throw new \Exception('BankCard are unavailable!', 404);
        }
        $reseller_bank_card = Arr::random($reseller_bank_cards);

        $merchant_deposit = $this->model::create([
            'merchant_id' => $merchant->id,
            'reseller_id' => $reseller_bank_card->reseller_id,
            'reseller_bank_card_id' => $reseller_bank_card->reseller_bank_card_id,
            'merchant_order_id' => $request->merchant_order_id,
            'player_id' => $request->get('player_id', 0),
            'method' => $request->method,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'status' => MerchantDeposit::STATUS['PENDING'],
            'callback_url' => $request->get('callback_url', $merchant->callback_url),
        ]);

        return $this->response->item($merchant_deposit, new DepositTransformer([
            'pay_url' => $merchant_deposit->payUrl
        ]));
    }

    /**
     * Update deposit extra information
     *
     * @param \Dingo\Api\Http\Request $request
     * @method PUT|PATCH
     * @return json
     */
    public function update(Request $request)
    {
        $merchant = $this->validateSign($request, [
            'merchant_order_id',
            'time',
        ]);
        $this->validate($request, [
            'time' => 'required|numeric',
            'currency' => 'required|in:' .
                implode(',', array_keys(app(\App\Settings\CurrencySetting::class)->currency)),
            'sender_mobile_number' => 'required_if:currency,BDT|numeric'
        ]);
        $m = $this->model::with(['paymentChannel'])->where([
            'merchant_id' => $merchant->id,
            'merchant_order_id' => $request->merchant_order_id,
        ])->firstOrFail();
        if ($m->status != MerchantDeposit::STATUS['PENDING']) {
            throw new \Exception('Status is not allowed to update', 401);
        }
        switch ($request->currency) {
            case 'BDT':
                $m->paymentChannel->validate([
                    'wallet_number' => $request->sender_mobile_number
                ]);
                $m->update([
                    'extra' => ['sender_mobile_number' => $request->sender_mobile_number] + $m->extra
                ]);
                break;
            default:
                throw new \Exception('Currency is not supported', 405);
        }

        return $this->success();
    }

    /**
     * Get payment page
     *
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return html
     */
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
        $steps = [
            ['icon' => 'fas fa-money-check-alt', 'label' => 'Transfer', 'status' => 1],
        ];
        switch ($deposit->status) {
            case MerchantDeposit::STATUS['PENDING']:
                $steps[] = ['label' => 'Confirm', 'status' => 0];
                break;
            case MerchantDeposit::STATUS['EXPIRED']:
                $steps[] = ['label' => 'Confirm', 'status' => 0];
                break;
            case MerchantDeposit::STATUS['APPROVED']:
            case MerchantDeposit::STATUS['ENFORCED']:
                $steps[] = ['label' => 'Confirm', 'status' => 2];
                break;
            case MerchantDeposit::STATUS['REJECTED']:
            case MerchantDeposit::STATUS['CANCELED']:
                $steps[] = ['label' => 'Confirm', 'status' => -1];
                break;
        }

        return view(strtolower("{$deposit->method}s.{$channel->name}.{$channel->currency}"), [
            'deposit' => $deposit,
            'channel' => $channel,
            'attributes' => $deposit->resellerBankCard->attributes,
            'steps' => $steps,
            'amount' => $deposit->amount,
        ]);
    }
}

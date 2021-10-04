<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Trait\SignValidator;
use App\Models\Reseller;
use App\Models\MerchantDeposit;
use App\Models\MerchantWithdrawal;
use App\Models\PaymentChannel;
use App\Transformers\Api\WithdrawalTransformer;

/**
 * @group Withdraw API
 *
 * Before using API, make sure you have an merchant acount.
 * <h3>Status</h3>
 * <table>
 * <thead>
 * <tr>
 * <td>Created</td>
 * <td>Pending</td>
 * <td>Finished</td>
 * <td>Rejected</td>
 * <td>Approved</td>
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
class WithdrawalController extends Controller
{
    use SignValidator;

    protected $model = MerchantWithdrawal::class;

    protected $transformer = WithdrawalTransformer::class;

    /**
     * Get withdrawal list
     *
     * This endpoint lets you get withdrawal list.
     *
     * @authenticated
     * @queryParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @queryParam per_page number Page count. Default: 10, Maximum: 100. Example: 1
     * @queryParam page number Set Page Number. Default: 1. Example: 1
     * @queryParam filter[status] number Filter status of withdrawal. Example: 1
     * @queryParam sign string required Signature. Example: 7d61c7fbce30dcdcefa8a9353a093656
     * @transformerCollection App\Transformers\Api\withdrawalTransformer
     * @transformerModel App\Models\Merchantwithdrawal
     * @transformerPaginator League\Fractal\Pagination\IlluminatePaginatorAdapter 1
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
        $m = QueryBuilder::for($this->model)
            ->allowedFilters([
                'merchant_order_id',
                'status'
            ])
            ->where('merchant_id', $merchant->id)
            ->paginate($this->perPage);

        return $this->response->withPaginator($m, new WithdrawalTransformer);
    }

    /**
     * Get a withdrawal
     *
     * This endpoint lets you get a withdrawal.
     *
     * @authenticated
     * @urlParam id required The Merchant Order ID of the withdrawal. Example: 9798223690986
     * @queryParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @queryParam sign string required Signature. Example: e38c3a02a3d9757c912d0dc6240a5c88
     * @transformer App\Transformers\Api\WithdrawalTransformer
     * @transformerModel App\Models\MerchantWithdrawal
     * @response status=404 scenario="not found"
     * {"message": "No query results for model [App\\Models\\MerchantWithdrawal]."}
     */
    public function show(Request $request)
    {
        $merchant = $this->validateSign($request);
        $m = $this->model::where([
            'merchant_id' => $merchant->id,
            'merchant_order_id' => $this->parameters('withdrawal')
        ])->firstOrFail();

        return $this->response->item($m, new WithdrawalTransformer);
    }

    /**
     * Create a withdrawal
     *
     * This endpoint lets you create a withdrawal.
     * <h3>Supported channels And required request field</h3>
     * <table>
     * <thead>
     * <tr>
     * <td>Currency</td>
     * <td>Channel</td>
     * <td>Field</td>
     * </tr>
     * </thead>
     * <tr>
     * <td>INR</td>
     * <td>NETBANK</td>
     * <td>account_number, account_name, ifsc_code</td>
     * </tr>
     * <tr>
     * <td>INR</td>
     * <td>UPI</td>
     * <td>upi_id</td>
     * </tr>
     * <tr>
     * <td>VND</td>
     * <td>NETBANK</td>
     * <td>account_number,account_name,bank_name</td>
     * </tr>
     * </table>
     *
     * @authenticated
     * @bodyParam uuid string required The Merchant UUID. Example: 224d4a1f-6fc5-4039-bd81-fcbc7f88c659
     * @bodyParam merchant_order_id string required The order id created by merchant. Example: 97982236909861
     * @bodyParam currency string required The currency of the withdrawal. Example: INR
     * @bodyParam channel string required Payment channel of the withdrawal. Example: UPI
     * @bodyParam upi_id string required Payment channel required field, various with different channel,
     * please refer the table above. Example: test1234@upi
     * @bodyParam amount string required Amount of the withdrawal. Example: 500
     * @bodyParam callback_url url required Callback URL, Example: :base_url/demos/callback
     * @bodyParam sign string required Signature. Example: fe0362897e797b33582a5934912952b9
     * @transformer App\Transformers\Api\WithdrawalTransformer
     * {"pay_url":":base_url/pay/withdrawals?uuid=224d4a1f-6fc5-4039-bd81-fcbc7f88c659&merchant_order_id=97982236909861&time=1633170173&sign=b4bf486861328f5a5dd43afe83958fdf"}
     * @transformerModel App\Models\MerchantWithdrawal
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
     * // When withdrawal order is approved, system will send callback request to the callback
     * // url of the order, the following shows callback request payload, Please verify
     * // the signature by your api key and apply the validateSign method.
     * {
     *     "name": "Test Merchant",
     *     "order_id": "N18t1@5iIxqZ4IXb",
     *     "merchant_order_id": "97982236909861",
     *     "amount": "500.0000",
     *     "currency": "INR",
     *     "status": 4,
     *     "upi_id": "test1234@upi",
     *     "callback_url": ":base_url/demos/callback",
     *     "sign": "09508cdf7f1d089e108d462d182204e6"
     * }
     * @callback_response status=200
     * // System expects to receive the following response format,
     * // otherwise callback request will be sent every 30 seconds, until
     * // it reachs the maximum limit.
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
                Rule::unique('merchant_withdrawals')->where(function ($query) use ($merchant) {
                    return $query->where('merchant_id', $merchant->id);
                }),
            ],
            'currency' => 'required|in:' . implode(',', array_keys($cs->currency)),
            'channel' => 'required',
            'amount' => 'required|numeric',
            'callback_url' => 'required|url'
        ]);
        $channel = PaymentChannel::where([
            'payout->status' => true,
            'currency' => $request->currency,
            'name' => $request->channel
        ])->firstOrFail();

        if ($merchant->getWithdrawalCredit($request->currency) < $request->amount) {
            throw new \Exception("Amount exceed your credit!", 405);
        }

        if ($request->amount < $channel->payout->min || $request->amount > $channel->payout->max) {
            throw new \Exception(
                "Amount is not in range[{$channel->payout->min}, {$channel->payout->max}]!",
                405
            );
        }
        $attributes = $channel->validate($request->all());

        $sql = "WITH reseller_channels AS (
            SELECT
                r.id AS id,
                r.currency AS currency,
                COUNT(DISTINCT md.id) AS payin,
                COUNT(DISTINCT mw.id) AS payout,
                r.pending_limit AS pending_limit 
            FROM
                resellers AS r
                LEFT JOIN merchant_withdrawals AS mw ON mw.reseller_id = r.id 
                LEFT JOIN reseller_bank_cards AS rbc ON rbc.reseller_id = r.id
                LEFT JOIN merchant_deposits AS md ON md.reseller_bank_card_id = rbc.id
                AND md.status IN (:md_status) AND md.updated_at BETWEEN :md_start AND :md_end
            WHERE
                r.currency = '{$request->currency}'
                AND r.STATUS = :r_status
                AND r.LEVEL = :r_level
                GROUP BY r.id
            )
            SELECT
                * 
            FROM
                reseller_channels
            WHERE payout < pending_limit 
            ORDER BY payin DESC";
        // dd($sql);
        $resellers = DB::select($sql, [
            'r_status' => Reseller::STATUS['ACTIVE'],
            'r_level' => Reseller::LEVEL['RESELLER'],
            'md_status' => implode(',', [
                MerchantDeposit::STATUS['APPROVED'],
                MerchantDeposit::STATUS['ENFORCED']
            ]),
            'md_start' => Carbon::now()->subHours(24),
            'md_end' => Carbon::now(),
        ]);
        // dd($resellers);
        if (empty($resellers)) {
            throw new \Exception('Channel is unavailable!', 404);
        }
        $reseller = Arr::first($resellers);

        DB::beginTransaction();
        try {
            $merchant_withdrawal = $this->model::create([
                'merchant_id' => $merchant->id,
                'reseller_id' => $reseller->id,
                'payment_channel_id' => $channel->id,
                'merchant_order_id' => $request->merchant_order_id,
                'attributes' => $attributes,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => MerchantWithdrawal::STATUS['PENDING'],
                'callback_url' => $request->callback_url,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return $this->response->item($merchant_withdrawal, new WithdrawalTransformer([
            'pay_url' => $merchant_withdrawal->payUrl
        ]));
    }

    public function pay(Request $request)
    {
        $merchant = $this->validateSign($request);
        $this->validate($request, [
            'time' => 'required|numeric',
        ]);
        $withdrawal = $this->model::with(['merchant', 'paymentChannel'])->where([
            'merchant_id' => $merchant->id,
            'merchant_order_id' => $request->merchant_order_id,
        ])->firstOrFail();
        $channel = $withdrawal->paymentChannel;
        $steps = [
            ['icon' => 'fas fa-money-check-alt', 'label' => 'Transfer', 'status' => 1],
        ];
        switch ($withdrawal->status) {
            case MerchantWithdrawal::STATUS['PENDING']:
                $steps[] = ['label' => 'Confirm', 'status' => 0];
                break;
            case MerchantWithdrawal::STATUS['EXPIRED']:
                $steps[] = ['label' => 'Confirm', 'status' => 0];
                break;
            case MerchantWithdrawal::STATUS['APPROVED']:
            case MerchantWithdrawal::STATUS['ENFORCED']:
                $steps[] = ['label' => 'Confirm', 'status' => 2];
                break;
            case MerchantWithdrawal::STATUS['REJECTED']:
            case MerchantWithdrawal::STATUS['CANCELED']:
                $steps[] = ['label' => 'Confirm', 'status' => -1];
                break;
        }

        return view(strtolower("payouts.{$channel->name}.{$channel->currency}"), [
            'withdrawal' => $withdrawal,
            'channel' => $channel,
            'attributes' => $withdrawal->attributes,
            'steps' => $steps,
            'amount' => number_format($withdrawal->amount, 2, '.', ''),
        ]);
    }
}
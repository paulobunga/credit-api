<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Dingo\Api\Http\Request;
use App\Models\Reseller;
use App\Models\ResellerBankCard;
use App\Models\ResellerDeposit;
use App\Models\ResellerWithdrawal;
use App\Models\MerchantDeposit;
use App\Models\Transaction;
use App\Filters\JsonColumnFilter;
use App\Http\Controllers\Controller;

/**
 * Reseller Endpoint
 */
class ResellerController extends Controller
{
    protected $model = Reseller::class;

    protected $transformer = \App\Transformers\Admin\ResellerTransformer::class;

    /**
     * Get agent list
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $m = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('level'),
                AllowedFilter::exact('currency'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('payin_status', new JsonColumnFilter('payin->status')),
                AllowedFilter::custom('payout_status', new JsonColumnFilter('payout->status')),
            ])
            ->allowedSorts([
                'id',
                'level',
                'name',
                'username',
                'phone',
                'credit',
                'coin',
                'downline_slot',
                'status'
            ]);

        return $this->paginate($m, $this->transformer);
    }

    /**
     * Create an agent
     *
     * @param \Dingo\Api\Http\Request $request
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'level' => 'required|between:' . implode(',', [
                Reseller::LEVEL['REFERRER'],
                Reseller::LEVEL['RESELLER'],
            ]),
            'upline' => [
                'required_unless:level,' . Reseller::LEVEL['REFERRER'],
            ],
            'name' => 'required|unique:resellers,name',
            'username' => 'required|unique:resellers,username',
            'phone' => 'required',
            'currency' => 'required_if:level,' . Reseller::LEVEL['REFERRER'],
            'password' => 'required|confirmed',
        ]);

        if ($request->level != Reseller::LEVEL['REFERRER']) {
            $upline = Reseller::findOrFail($request->upline);
            $uplines = array_merge($upline->uplines, [$upline->id]);
            $currency = $upline->currency;
        } else {
            $uplines = [];
            $currency = $request->currency;
        }
        $reseller_setting = app(\App\Settings\ResellerSetting::class);
        $agent_setting = app(\App\Settings\AgentSetting::class);
        $currency_setting = app(\App\Settings\CurrencySetting::class);

        $reseller = $this->model::create([
            'level' => $request->level,
            'upline_id' => $upline->id ?? 0,
            'uplines' => $uplines,
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'currency' => $currency,
            'password' => $request->password,
            'payin' => [
                'commission_percentage' => $currency_setting->getCommissionPercentage(
                    $currency,
                    $request->level
                ),
                'pending_limit' => $reseller_setting->getDefaultPendingLimit($request->level),
                'status' => true,
                'auto_sms_approval' => false
            ],
            'payout' => [
                'commission_percentage' => $currency_setting->getCommissionPercentage(
                    $currency,
                    $request->level
                ),
                'pending_limit' => $reseller_setting->getDefaultPendingLimit($request->level),
                'status' => true,
                'daily_amount_limit' => 50000,
            ],
            'downline_slot' => $agent_setting->getDefaultDownLineSlot($request->level),
            'status' => ($request->level == Reseller::LEVEL['RESELLER']) ?
                Reseller::STATUS['INACTIVE'] :
                Reseller::STATUS['ACTIVE'],
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    /**
     * Update an agent via id
     *
     * @param \Dingo\Api\Http\Request
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $reseller = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'level' => 'required',
            'name' => "required|unique:resellers,name,{$reseller->id}",
            'username' => "required|unique:resellers,username,{$reseller->id}",
            'phone' => "required",
            'downline_slot' =>
            'required_with:level,1,2|numeric|max:' . app(\App\Settings\AgentSetting::class)->max_downline_slot,
            'status' => 'required|numeric|in:' . implode(',', Reseller::STATUS),
            'payin' => 'required',
            'payout' => 'required',
        ]);
        $reseller->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'payin' => $request->payin,
            'payout' => $request->payout,
            'downline_slot' => in_array($request->level, [
                Reseller::LEVEL['AGENT_MASTER'],
                Reseller::LEVEL['AGENT']
            ]) ? $request->downline_slot : 0,
            'status' => $request->status
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    /**
     * Delete an agent via id
     * @param \Dingo\Api\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $reseller = $this->model::findOrFail($this->parameters('reseller'));
        if ($reseller->id == 1) {
            throw new \Exception('Default referrer cannot be removed!', 405);
        }
        $reseller->delete();

        return $this->success();
    }

    /**
     * Top up agent credit or coin via id
     * @param \Dingo\Api\Http\Request
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function deposit(Request $request)
    {
        $reseller = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'type' => 'required|in:' . implode(',', ResellerDeposit::TYPE),
            'amount' => 'required|numeric|min:1',
            'extra' => 'array|required',
            'extra.payment_type' => 'required',
            'extra.reason' => 'required',
            'extra.remark' => 'required'
        ]);

        if ($request->type == ResellerDeposit::TYPE['CREDIT']) {
            $transaction_type = Transaction::TYPE['ADMIN_TOPUP_CREDIT'];
        } elseif ($request->type == ResellerDeposit::TYPE['COIN']) {
            $transaction_type = Transaction::TYPE['ADMIN_TOPUP_COIN'];
        } else {
            throw new \Exception('Unsupported transaction type');
        }
        $ability = auth()->user()->can('admin.reseller_deposits.update');

        $reseller->deposits()->create([
            'reseller_id' => $reseller->id,
            'audit_admin_id' => $ability ? auth()->id() : 0,
            'type' => $request->type,
            'transaction_type' => $transaction_type,
            'amount' => $request->amount,
            'extra' => array_merge(
                $request->extra,
                ['memo' => $ability ? 'success' : '', 'creator' => auth()->id()]
            ),
            'status' => $ability ?
                ResellerDeposit::STATUS['APPROVED'] :
                ResellerDeposit::STATUS['PENDING']
        ]);

        return $this->response->item($reseller->refresh(), $this->transformer);
    }

    /**
     * Withdraw agent credit or coin via id
     * @param \Dingo\Api\Http\Request
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function withdrawal(Request $request)
    {
        $reseller = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'type' => 'required|in:' . implode(',', ResellerWithdrawal::TYPE),
            'amount' => 'required',
            'extra' => 'array|required',
            'extra.payment_type' => 'required',
            'extra.reason' => 'required',
            'extra.remark' => 'required'
        ]);
        if ($request->type == ResellerWithdrawal::TYPE['CREDIT']) {
            $transaction_type = Transaction::TYPE['ADMIN_WITHDRAW_CREDIT'];
        } elseif ($request->type == ResellerWithdrawal::TYPE['COIN']) {
            $transaction_type = Transaction::TYPE['ADMIN_WITHDRAW_COIN'];
        } else {
            throw new \Exception('Unsupported transaction type');
        }
        $ability = auth()->user()->can('admin.reseller_withdrawals.update');

        $reseller->withdrawals()->create([
            'reseller_id' => $reseller->id,
            'audit_admin_id' => $ability ? auth()->id() : 0,
            'type' => $request->type,
            'transaction_type' => $transaction_type,
            'amount' => $request->amount,
            'extra' => array_merge(
                $request->extra,
                ['memo' => $ability ? 'success' : '', 'creator' => auth()->id()]
            ),
            'status' => $ability ?
                ResellerWithdrawal::STATUS['APPROVED'] :
                ResellerWithdrawal::STATUS['PENDING']
        ]);

        return $this->response->item($reseller->refresh(), $this->transformer);
    }

    /**
     * Reset agent password via id
     * @param \Dingo\Api\Http\Request
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'password' => 'required|confirmed',
        ]);
        $m->password = $request->password;
        $m->save();

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Make up agent pay in order
     * @param \Dingo\Api\Http\Request $request
     * @return json
     */
    public function makeUp(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'merchant_id' => 'required|exists:merchants,id',
            'reseller_bank_card_id' => 'required',
            'method' => 'required',
            'amount' => 'required|numeric',
            'reference_id' => 'required'
        ]);
        $reseller_bank_card = ResellerBankCard::where([
            'reseller_id' => $m->id,
            'id' => $request->reseller_bank_card_id,
        ])->firstOrFail();
        if (!in_array($request->method, $reseller_bank_card->paymentChannel->paymentMethods)) {
            throw new \Exception('Method is not supported!', 405);
        }
        MerchantDeposit::create([
            'merchant_id' => $request->merchant_id,
            'reseller_id' => $m->id,
            'reseller_bank_card_id' => $request->reseller_bank_card_id,
            'merchant_order_id' => Str::uuid(),
            'method' => $request->method,
            'amount' => $request->amount,
            'currency' => $m->currency,
            'status' => MerchantDeposit::STATUS['MAKEUP'],
            'callback_url' => '',
            'callback_status' => MerchantDeposit::CALLBACK_STATUS['FINISH'],
            'extra' => [
                'admin_id' => auth()->id(),
                'reference_id' => $request->reference_id
            ]
        ]);

        return $this->success();
    }
}

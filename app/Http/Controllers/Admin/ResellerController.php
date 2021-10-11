<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Dingo\Api\Http\Request;
use App\Models\Merchant;
use App\Models\Reseller;
use App\Models\ResellerBankCard;
use App\Models\ResellerDeposit;
use App\Models\ResellerWithdrawal;
use App\Models\MerchantDeposit;
use App\Models\Transaction;

/**
 * Reseller Endpoint
 */
class ResellerController extends Controller
{
    protected $model = Reseller::class;

    protected $transformer = \App\Transformers\Admin\ResellerTransformer::class;

    /**
     * Get agent lists
     * @param \Dingo\Api\Http\Request
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $resellers = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('level'),
                AllowedFilter::exact('currency'),
                AllowedFilter::exact('status')
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
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($resellers, $this->transformer);
    }

    /**
     * Create an agent
     * @param \Dingo\Api\Http\Request
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'level' => 'required|between:0,3',
            'upline' => 'required_unless:level,0',
            'name' => 'required|unique:resellers,name',
            'username' => 'required|unique:resellers,username',
            'phone' => 'required',
            'currency' => 'required',
            'password' => 'required|confirmed',
        ]);
        $reseller_setting = app(\App\Settings\ResellerSetting::class);
        $agent_setting = app(\App\Settings\AgentSetting::class);
        $currency_setting = app(\App\Settings\CurrencySetting::class);

        $reseller = $this->model::create([
            'level' => $request->level,
            'upline' => $request->get('upline', 0),
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'currency' => $request->currency,
            'password' => $request->password,
            'commission_percentage' => $currency_setting->getCommissionPercentage(
                $request->currency,
                $request->level
            ),
            'pending_limit' => $reseller_setting->getDefaultPendingLimit($request->level),
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
            $this->validate($request, [
                'amount' => 'numeric|between:1,' . $reseller->withdrawalCredit
            ]);
            $transaction_type = Transaction::TYPE['ADMIN_WITHDRAW_CREDIT'];
        } elseif ($request->type == ResellerWithdrawal::TYPE['COIN']) {
            $this->validate($request, [
                'amount' => 'numeric|between:1,' . $reseller->withdrawalCoin
            ]);
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
     * @param \Dingo\Api\Http\Request
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function makeUp(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'merchant_id' => 'required',
            'reseller_bank_card_id' => 'required',
            'method' => 'required',
            'amount' => 'required|numeric'
        ]);
        $merchant = Merchant::findOrFail($request->merchant_id);
        $reseller_bank_card = ResellerBankCard::where([
            'reseller_id' => $m->id,
            'id' => $request->reseller_bank_card_id,
        ])->firstOrFail();
        if (!in_array($request->method, $reseller_bank_card->paymentChannel->paymentMethods)) {
            throw new \Exception('Method is not supported!', 405);
        }
        DB::beginTransaction();
        try {
            MerchantDeposit::create([
                'merchant_id' => $request->merchant_id,
                'reseller_id' => $m->id,
                'reseller_bank_card_id' => $request->reseller_bank_card_id,
                'merchant_order_id' => Str::uuid(),
                'method' => $request->method,
                'amount' => $request->amount,
                'currency' => $m->currency,
                'status' => MerchantDeposit::STATUS['MAKEUP'],
                'callback_url' => $merchant->callback_url,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
        return $this->success();
    }
}

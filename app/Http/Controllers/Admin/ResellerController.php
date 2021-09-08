<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Reseller;
use App\Models\ResellerDeposit;
use App\Models\ResellerWithdrawal;
use App\Models\Transaction;

class ResellerController extends Controller
{
    protected $model = Reseller::class;

    protected $transformer = \App\Transformers\Admin\ResellerTransformer::class;

    /**
     * Get reseller lists
     *
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
                'pending_limit',
                'commission_percentage',
                'downline_slot',
                'status'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($resellers, $this->transformer);
    }

    /**
     * Create a reseller
     *
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
     * Update a reseller via id
     *
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
            'commission_percentage' => 'required|numeric',
            'pending_limit' =>
            'required|numeric|max:' . app(\App\Settings\ResellerSetting::class)->max_pending_limit,
            'downline_slot' =>
            'required_with:level,1,2|numeric|max:' . app(\App\Settings\AgentSetting::class)->max_downline_slot,
            'status' => 'required|numeric|in:' . implode(',', Reseller::STATUS),
        ]);
        $reseller->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'commission_percentage' => $request->commission_percentage,
            'pending_limit' => $request->pending_limit,
            'downline_slot' => in_array($request->level, [1, 2]) ? $request->downline_slot : 0,
            'status' => $request->status
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    /**
     * Delete a reseller via id
     *
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
     * Top up reseller create or coin via id
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function deposit(Request $request)
    {
        $reseller = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'type' => 'required|in:' . implode(',', ResellerDeposit::TYPE),
            'transaction_type' => 'required|between:0,1',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($request->type == ResellerDeposit::TYPE['CREDIT'] && $request->transaction_type == 0) {
            $transaction_type = Transaction::TYPE['RESELLER_TOPUP_CREDIT'];
        } elseif ($request->type == ResellerDeposit::TYPE['CREDIT'] && $request->transaction_type == 1) {
            $transaction_type = Transaction::TYPE['ADMIN_TOPUP_CREDIT'];
        } elseif ($request->type == ResellerDeposit::TYPE['COIN'] && $request->transaction_type == 1) {
            $transaction_type = Transaction::TYPE['ADMIN_TOPUP_COIN'];
        } else {
            throw new \Exception('Unsupported transaction type');
        }

        if ($request->type == ResellerDeposit::TYPE['CREDIT'] &&  $request->transaction_type == 0) {
            $this->validate($request, [
                'payment_type' => 'required',
            ]);
            $extra = [
                'payment_type' => $request->payment_type
            ];
        } else {
            $this->validate($request, [
                'reason' => 'required',
            ]);
            $extra = [
                'reason' => $request->reason
            ];
        }
        $reseller->deposits()->create([
            'reseller_id' => $reseller->id,
            'audit_admin_id' => Auth::id(),
            'type' => $request->type,
            'transaction_type' => $transaction_type,
            'amount' => $request->amount,
            'extra' => $extra,
            'status' => ResellerDeposit::STATUS['APPROVED']
        ]);

        return $this->response->item($reseller->refresh(), $this->transformer);
    }

    /**
     * Withdraw reseller create or coin via id
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function withdrawal(Request $request)
    {
        $reseller = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'type' => 'required|in:' . implode(',', ResellerWithdrawal::TYPE),
            'transaction_type' => 'required|between:0,1',
            'amount' => 'required',
            'reason' => 'required',
        ]);
        if ($request->type == ResellerWithdrawal::TYPE['CREDIT'] && $request->transaction_type == 0) {
            $this->validate($request, ['amount' => 'numeric|between:1,' . $reseller->credit]);
            $transaction_type = Transaction::TYPE['RESELLER_WITHDRAW_CREDIT'];
        } elseif ($request->type == ResellerWithdrawal::TYPE['CREDIT'] && $request->transaction_type == 1) {
            $this->validate($request, ['amount' => 'numeric|between:1,' . $reseller->credit]);
            $transaction_type = Transaction::TYPE['ADMIN_WITHDRAW_CREDIT'];
        } elseif ($request->type == ResellerWithdrawal::TYPE['COIN'] && $request->transaction_type == 0) {
            $this->validate($request, ['amount' => 'numeric|between:1,' . $reseller->coin]);
            $transaction_type = Transaction::TYPE['RESELLER_WITHDRAW_COIN'];
        } elseif ($request->type == ResellerWithdrawal::TYPE['COIN'] && $request->transaction_type == 1) {
            $this->validate($request, ['amount' => 'numeric|between:1,' . $reseller->coin]);
            $transaction_type = Transaction::TYPE['ADMIN_WITHDRAW_COIN'];
        } else {
            throw new \Exception('Unsupported transaction type');
        }

        $reseller->withdrawals()->create([
            'reseller_id' => $reseller->id,
            'audit_admin_id' => Auth::id(),
            'type' => $request->type,
            'transaction_type' => $transaction_type,
            'amount' => $request->amount,
            'extra' => [
                'reason' => $request->reason
            ],
            'status' => ResellerWithdrawal::STATUS['APPROVED']
        ]);

        return $this->response->item($reseller->refresh(), $this->transformer);
    }

    /**
     * Reset reseller password via id
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $merchant = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'password' => 'required|confirmed',
        ]);
        $merchant->password = $request->password;
        $merchant->save();

        return $this->response->item($merchant, $this->transformer);
    }
}

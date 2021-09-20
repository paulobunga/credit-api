<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerWithdrawal;

class ResellerWithdrawalController extends Controller
{
    protected $model = ResellerWithdrawal::class;

    protected $transformer = \App\Transformers\Admin\ResellerWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $reseller_withdrawals = QueryBuilder::for($this->model)
            ->with([
                'reseller',
                'auditAdmin',
            ])
            ->join('resellers', 'resellers.id', '=', 'reseller_withdrawals.reseller_id')
            ->join('admins', 'admins.id', '=', 'reseller_withdrawals.audit_admin_id')
            ->select(
                'reseller_withdrawals.*',
                'resellers.name',
                'admins.name AS admin'
            )
            ->allowedFilters([
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('reseller_withdrawals.created_at', $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'name',
                'order_id',
                'amount',
                'status'
            ]);

        return $this->paginate($reseller_withdrawals, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller_withdrawal = $this->model::with('reseller')->findOrFail($this->parameters('reseller_withdrawal'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);
        $reseller = $reseller_withdrawal->reseller;
        if ($request->status == ResellerWithdrawal::STATUS['APPROVED']) {
            if ($reseller_withdrawal->type == ResellerWithdrawal::TYPE['CREDIT']) {
                if ($reseller_withdrawal->amount > $reseller->credit) {
                    throw new \Exception('exceed credit', 405);
                }
            } elseif ($reseller_withdrawal->type == ResellerWithdrawal::TYPE['COIN']) {
                if ($reseller_withdrawal->amount > $reseller->coin) {
                    throw new \Exception('exceed coin', 405);
                }
            }
        }
        $reseller_withdrawal->update([
            'audit_admin_id' => $request->admin_id,
            'status' => $request->status,
            'extra' => [
                'reason' => 'Withdraw'
            ]
        ]);

        return $this->response->item($reseller_withdrawal->refresh(), $this->transformer);
    }
}

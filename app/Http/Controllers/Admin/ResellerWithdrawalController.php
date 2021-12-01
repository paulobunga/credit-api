<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerWithdrawal;
use App\Filters\Admin\ResellerWithdrawalCreatedAtBetweenFilter;

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
            ->leftjoin('admins', 'admins.id', '=', 'reseller_withdrawals.audit_admin_id')
            ->select(
                'reseller_withdrawals.*',
                'resellers.name',
                'admins.name AS admin',
                'resellers.currency AS currency',
            )
            ->allowedFilters([
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('created_at_between', new ResellerWithdrawalCreatedAtBetweenFilter),
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
        $m = $this->model::with('reseller')->findOrFail($this->parameters('reseller_withdrawal'));
        $this->validate($request, [
            'status' => 'required|numeric',
            'extra' => 'required|array'
        ]);
        if ($m->status != ResellerWithdrawal::STATUS['PENDING']) {
            throw new \Exception('Status is not allowed to changed!', 405);
        }
        $m->update([
            'audit_admin_id' => auth()->id(),
            'status' => $request->status,
            'extra' => array_merge(
                $m->extra,
                $request->extra
            )
        ]);

        return $this->response->item($m->refresh(), $this->transformer);
    }
}

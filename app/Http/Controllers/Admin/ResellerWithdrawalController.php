<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerWithdrawalController extends Controller
{
    protected $model = \App\Models\ResellerWithdrawal::class;
    protected $transformer = \App\Transformers\Admin\ResellerWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $reseller_withdrawals = QueryBuilder::for($this->model)
            ->with([
                'reseller'
            ])
            ->join('resellers', 'resellers.id', '=', 'reseller_withdrawals.reseller_id')
            ->select('reseller_withdrawals.*', 'resellers.name')
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
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($reseller_withdrawals, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller_withdrawal = $this->model::findOrFail($this->parameters('reseller_withdrawal'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);
        $reseller_withdrawal->update([
            'status' => $request->status,
            'info' => [
                'admin_id' => $request->admin_id
            ]
        ]);

        return $this->response->item($reseller_withdrawal, $this->transformer);
    }
}

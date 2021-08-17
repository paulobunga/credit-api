<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class ResellerDepositController extends Controller
{
    protected $model = \App\Models\ResellerDeposit::class;

    protected $transformer = \App\Transformers\Admin\ResellerDepositTransformer::class;

    public function index(Request $request)
    {
        $reseller_deposits = QueryBuilder::for($this->model)
            ->with([
                'reseller',
                'auditAdmin'
            ])
            ->join('resellers', 'resellers.id', '=', 'reseller_deposits.reseller_id')
            ->join('admins', 'admins.id', '=', 'reseller_deposits.audit_admin_id')
            ->select(
                'reseller_deposits.*',
                'resellers.name AS name',
                'admins.name AS admin',
                'resellers.currency AS currency',
            )
            ->allowedFilters([
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('reseller_deposits.created_at', $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'name',
                'transaction_type',
                'admin',
                'order_id',
                'type',
                'amount',
                'currency',
                'status',
                'created_at'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($reseller_deposits, $this->transformer);
    }
}

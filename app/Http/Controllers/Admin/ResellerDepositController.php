<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerDeposit;
use App\Trait\UserTimezone;

class ResellerDepositController extends Controller
{
    use UserTimezone;

    protected $model = ResellerDeposit::class;

    protected $transformer = \App\Transformers\Admin\ResellerDepositTransformer::class;

    protected $db_timezone;

    protected $user_timezone;

    public function __construct()
    {
        parent::__construct();
        $this->db_timezone = env('DB_TIMEZONE');
        $this->user_timezone = $this->userTimezoneOffset();
    }

    public function index(Request $request)
    {
        $reseller_deposits = QueryBuilder::for($this->model)
            ->with([
                'reseller',
                'auditAdmin'
            ])
            ->join('resellers', 'resellers.id', '=', 'reseller_deposits.reseller_id')
            ->leftjoin('admins', 'admins.id', '=', 'reseller_deposits.audit_admin_id')
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
                    fn ($query, $v) => $query->whereRaw("CONVERT_TZ(reseller_deposits.created_at, '{$this->db_timezone}', '{$this->user_timezone}') BETWEEN ? AND ?", $v)
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
            ]);

        return $this->paginate($reseller_deposits, $this->transformer);
    }

    public function update(Request $request)
    {
        $m = $this->model::with('reseller')->findOrFail($this->parameters('reseller_deposit'));
        if ($m->status != ResellerDeposit::STATUS['PENDING']) {
            throw new \Exception('Status is not allowed to modified', 405);
        }
        $this->validate($request, [
            'status' => 'required|numeric|in:' . implode(',', [
                ResellerDeposit::STATUS['APPROVED'],
                ResellerDeposit::STATUS['REJECTED'],
            ]),
            'extra' => 'required|array'
        ]);

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

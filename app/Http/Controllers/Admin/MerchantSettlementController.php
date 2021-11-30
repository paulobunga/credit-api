<?php

namespace App\Http\Controllers\Admin;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;
use App\Trait\UserTimezone;

class MerchantSettlementController extends Controller
{
    use UserTimezone;

    protected $model = \App\Models\MerchantSettlement::class;

    protected $transformer = \App\Transformers\Admin\MerchantSettlementTransformer::class;

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
        $merchant_settlements = QueryBuilder::for($this->model)
            ->with([
                'merchant'
            ])
            ->join('merchants', 'merchants.id', '=', 'merchant_settlements.merchant_id')
            ->select('merchant_settlements.*', 'merchants.name')
            ->allowedFilters([
                AllowedFilter::partial('name', 'merchants.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereRaw("CONVERT_TZ(merchant_settlements.created_at, '{$this->db_timezone}', '{$this->user_timezone}') BETWEEN ? AND ?", $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'name',
                'order_id',
                'amount',
                'currency',
                'status'
            ]);

        return $this->paginate($merchant_settlements, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant_settlement = $this->model::findOrFail($this->parameters('merchant_settlement'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);

        $merchant_settlement->update([
            'status' => $request->status,
            'extra' => [
                'admin_id' => $request->admin_id
            ]
        ]);

        return $this->response->item($merchant_settlement, $this->transformer);
    }
}

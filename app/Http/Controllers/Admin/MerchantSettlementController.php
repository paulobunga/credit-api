<?php

namespace App\Http\Controllers\Admin;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;
use App\Filters\Admin\MerchantSettlementCreatedAtBetweenFilter;

class MerchantSettlementController extends Controller
{
    protected $model = \App\Models\MerchantSettlement::class;

    protected $transformer = \App\Transformers\Admin\MerchantSettlementTransformer::class;

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
                AllowedFilter::custom('created_at_between', new MerchantSettlementCreatedAtBetweenFilter),
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class MerchantDepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Admin\MerchantDepositTransformer::class;

    public function index(Request $request)
    {
        $merchant_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_deposits, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant_deposit = $this->model::findOrFail($this->parameters('merchant_deposit'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);

        $merchant_deposit->update([
            'status' => $request->status,
            'info' => [
                'admin_id' => $request->admin_id
            ]
        ]);

        return $this->response->item($merchant_deposit, $this->transformer);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;

class ResellerWithdrawalController extends Controller
{
    protected $model = \App\Models\ResellerWithdrawal::class;
    protected $transformer = \App\Transformers\Admin\ResellerWithdrawalTransformer::class;

    public function index(Request $request)
    {
        $reseller_withdrawals = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::partial('name'),
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

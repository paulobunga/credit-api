<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerController extends Controller
{
    protected $model = \App\Models\Reseller::class;
    protected $transformer = \App\Transformers\Admin\ResellerTransformer::class;

    public function index()
    {
        $resellers = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($resellers, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:resellers,name',
            'username' => 'required|unique:resellers,username',
            'phone' => 'required|unique:resellers,phone',
            'password' => 'required|confirmed',
            'transaction_fee' => 'required|numeric',
            'pending_limit' => 'required|numeric',
            'status' => 'required|boolean'
        ]);
        $reseller = $this->model::create([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'password' => $request->password,
            'transaction_fee' => $request->transaction_fee,
            'pending_limit' => $request->pending_limit,
            'status' => $request->status,
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller = $this->model::where('name', urldecode($this->parameters('reseller')))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:resellers,name,{$reseller->id}",
            'username' => "required|unique:resellers,username,{$reseller->id}",
            'phone' => "required|unique:resellers,phone,{$reseller->id}",
            'transaction_fee' => 'required|numeric',
            'pending_limit' => 'required|numeric',
            'status' => 'required|boolean'
        ]);
        $reseller->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'transaction_fee' => $request->transaction_fee,
            'pending_limit' => $request->pending_limit,
            'status' => $request->status,
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $reseller = $this->model::where('name', urldecode($this->parameters('reseller')))->firstOrFail();
        $reseller->delete();

        return $this->success();
    }
}

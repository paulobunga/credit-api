<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerController extends Controller
{
    protected $model = \App\Models\Reseller::class;
    protected $transformer = \App\Transformers\ResellerTransformer::class;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function index()
    {
        $resellers = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($resellers, $this->transformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:resellers,name',
            'email' => 'required|unique:resellers,email',
            'phone' => 'required|unique:resellers,phone',
            'password' => 'required|confirmed',
            'transaction_fee' => 'numeric',
            'pending_limit' => 'numeric',
            'status' => 'boolean'
        ]);
        $reseller = $this->model::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'transaction_fee' => $request->transaction_fee,
            'pending_limit' => $request->pending_limit,
            'status' => $request->status,
        ]);
        return $this->response->item($reseller, $this->transformer);
    }

    public function destroy(String $name)
    {
        $reseller = $this->model::where('name', $name)->firstOrFail();
        $reseller->delete();
        return $this->success();
    }

    public function edit(Request $request, String $name)
    {
        $reseller = $this->model::where('name', urldecode($name))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:resellers,name,{$reseller->id}",
            'email' => "required|unique:resellers,email,{$reseller->id}",
            'phone' => "required|unique:resellers,phone,{$reseller->id}",
            'transaction_fee' => 'numeric',
            'pending_limit' => 'numeric',
            'status' => 'boolean'
        ]);
        $reseller->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'transaction_fee' => $request->transaction_fee,
            'pending_limit' => $request->pending_limit,
            'status' => $request->status,
        ]);
        return $this->success();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerDepositController extends Controller
{
    protected $model = \App\Models\ResellerDeposit::class;
    protected $transformer = \App\Transformers\ResellerDepositTransformer::class;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function index()
    {
        $reseller_deposits = QueryBuilder::for($this->model)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\ResellerFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($reseller_deposits, $this->transformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:reseller_deposits,name',
            'email' => 'required|unique:reseller_deposits,email',
            'phone' => 'required|unique:reseller_deposits,phone',
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
        $reseller_deposit = $this->model::where('name', urldecode($name))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:reseller_deposits,name,{$reseller_deposit->id}",
            'email' => "required|unique:reseller_deposits,email,{$reseller_deposit->id}",
            'phone' => "required|unique:reseller_deposits,phone,{$reseller_deposit->id}",
            'transaction_fee' => 'numeric',
            'pending_limit' => 'numeric',
            'status' => 'boolean'
        ]);
        $reseller_deposit->update([
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

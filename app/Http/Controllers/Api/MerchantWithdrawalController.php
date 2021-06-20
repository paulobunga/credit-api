<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class MerchantWithdrawalController extends Controller
{
    protected $model = \App\Models\MerchantWithdrawal::class;
    protected $transformer = \App\Transformers\MerchantWithdrawalTransformer::class;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function index()
    {
        $merchant_withdrawals = QueryBuilder::for($this->model)
            ->allowedFilters([
                // AllowedFilter::custom('name', new \App\Http\Filters\MerchantFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_withdrawals, $this->transformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:merchant_withdrawals,name',
            'email' => 'required|unique:merchant_withdrawals,email',
            'phone' => 'required|unique:merchant_withdrawals,phone',
            'password' => 'required|confirmed',
            'transaction_fee' => 'numeric',
            'pending_limit' => 'numeric',
            'status' => 'boolean'
        ]);
        $merchant = $this->model::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'transaction_fee' => $request->transaction_fee,
            'pending_limit' => $request->pending_limit,
            'status' => $request->status,
        ]);
        return $this->response->item($merchant, $this->transformer);
    }

    public function destroy(String $name)
    {
        $merchant = $this->model::where('name', $name)->firstOrFail();
        $merchant->delete();
        return $this->success();
    }

    public function edit(Request $request, String $name)
    {
        $merchant_deposit = $this->model::where('name', urldecode($name))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:merchant_withdrawals,name,{$merchant_deposit->id}",
            'email' => "required|unique:merchant_withdrawals,email,{$merchant_deposit->id}",
            'phone' => "required|unique:merchant_withdrawals,phone,{$merchant_deposit->id}",
            'transaction_fee' => 'numeric',
            'pending_limit' => 'numeric',
            'status' => 'boolean'
        ]);
        $merchant_deposit->update([
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

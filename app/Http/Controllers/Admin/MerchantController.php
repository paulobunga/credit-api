<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MerchantController extends Controller
{
    protected $model = \App\Models\Merchant::class;
    protected $transformer = \App\Transformers\Admin\MerchantTransformer::class;

    public function index(Request $request)
    {
        $merchants = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($merchants, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:merchants,name',
            'username' => 'required|unique:merchants,username',
            'password' => 'required|confirmed',
            'phone' => 'required',
            'transaction_fee' => 'required|numeric',
            'callback_url' => 'required',
            'status' => 'required|boolean'
        ]);
        $merchant = $this->model::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password,
            'phone' => $request->phone,
            'transaction_fee' => $request->transaction_fee,
            'callback_url' => $request->callback_url,
            'status' => $request->status
        ]);

        return $this->response->item($merchant, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant = $this->model::where('name', urldecode($this->parameters('merchant')))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:merchants,name,{$merchant->id}",
            'username' => "required|unique:merchants,username,{$merchant->id}",
            'phone' => 'required',
            'transaction_fee' => 'required|numeric',
            'callback_url' => 'required',
            'status' => 'required|boolean'
        ]);
        $merchant->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'transaction_fee' => $request->transaction_fee,
            'callback_url' => $request->callback_url,
            'status' => $request->status
        ]);

        return $this->response->item($merchant, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $merchant = $this->model::where('name', urldecode($this->parameters('merchant')))->firstOrFail();
        $merchant->delete();

        return $this->success();
    }

    public function renew()
    {
        $merchant = $this->model::where('name', urldecode($this->parameters('merchant')))->firstOrFail();
        $merchant->api_key = Str::random(30);
        $merchant->save();

        return $this->response->item($merchant, $this->transformer);
    }

    public function whitelist(Request $request)
    {
        $merchant = $this->model::where('id', $this->parameters('merchant'))->firstOrFail();
        $this->validate($request, [
            'ip' => 'array',
            'ip.*' => 'distinct|ipv4',
        ]);
        $merchant_white_lists = \App\Models\MerchantWhiteList::updateOrCreate(
            ['merchant_id' => $merchant->id],
            ['ip' => $request->ip],
        );

        return $this->response->item(
            $merchant_white_lists,
            \App\Transformers\Admin\MerchantWhiteListTransformer::class
        );
    }
}

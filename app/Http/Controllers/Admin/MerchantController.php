<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Str;
use App\Models\MerchantCredit;

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
            ->allowedSorts([
                'id',
                'name',
                'username',
                'phone',
                'uuid',
                'status',
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
            'callback_url' => 'required',
            'currency' => 'required|array',
            'currency.*.name' => 'required|distinct',
            'currency.*.value' => 'required|boolean',
            'currency.*.transaction_fee' => 'required|numeric',
            'status' => 'required|boolean'
        ]);
        $setting = app(\App\Settings\CurrencySetting::class);
        $currency = [];
        foreach ($request->currency as $c) {
            if (!$c['value']) {
                continue;
            }
            if (!in_array($c['label'], $setting->types)) {
                throw new \Exception('Unsuported currency type ' . $c['label'], 500);
            }
            $currency[] = $c;
        }
        if (count($currency) < 1) {
            throw new \Exception('Merchant must select at least one currency', 500);
        }

        $merchant = $this->model::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password,
            'phone' => $request->phone,
            'callback_url' => $request->callback_url,
            'status' => $request->status
        ]);

        foreach ($currency as $c) {
            \App\Models\MerchantCredit::updateOrCreate(
                ['merchant_id' => $merchant->id, 'currency' => $c['label']],
                ['transaction_fee' => $c['transaction_fee']],
            );
        }

        return $this->response->item($merchant, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant = $this->model::where('id', urldecode($this->parameters('merchant')))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:merchants,name,{$merchant->id}",
            'username' => "required|unique:merchants,username,{$merchant->id}",
            'phone' => 'required',
            'callback_url' => 'required',
            'status' => 'required|boolean'
        ]);
        $merchant->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'callback_url' => $request->callback_url,
            'status' => $request->status
        ]);

        return $this->response->item($merchant, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $merchant = $this->model::where('id', urldecode($this->parameters('merchant')))->firstOrFail();
        $merchant->delete();

        return $this->success();
    }

    public function renew()
    {
        $merchant = $this->model::where('id', urldecode($this->parameters('merchant')))->firstOrFail();
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

    public function fee(Request $request)
    {
        $merchant = $this->model::where('id', $this->parameters('merchant'))->firstOrFail();
        $this->validate($request, [
            'currency' => 'required|array',
            'currency.*.currency' => 'required|distinct',
            'currency.*.transaction_fee' => 'required|numeric',
        ]);
        foreach ($request->currency as $c) {
            MerchantCredit::where('currency', $c['currency'])
                ->where('merchant_id', $merchant->id)
                ->update(['transaction_fee' => $c['transaction_fee']]);
        }

        return $this->response->item($merchant, $this->transformer);
    }
}

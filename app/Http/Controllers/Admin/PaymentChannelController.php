<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Dingo\Api\Http\Request;
use App\Models\PaymentChannel;

class PaymentChannelController extends Controller
{
    protected $model = \App\Models\PaymentChannel::class;

    protected $transformer = \App\Transformers\Admin\PaymentChannelTransformer::class;

    public function index(Request $request)
    {
        $admins = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('currency'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'currency',
                'status',
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($admins, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:payment_channels,name',
            'payment_methods' => 'required|array|in:' . implode(',', PaymentChannel::METHOD),
            'currency' => 'required|in:' . implode(',', app(\App\Settings\CurrencySetting::class)->types),
            'banks' => 'array',
            'banks.*' => 'exists:banks,id',
            'status' => 'boolean'
        ]);

        $model = $this->model::create([
            'name' => $request->name,
            'payment_methods' => implode(',', $request->payment_methods),
            'currency' => $request->currency,
            'banks' => implode(',', $request->banks),
            'status' => $request->status,
        ]);

        return $this->response->item($model, $this->transformer);
    }

    public function update(Request $request)
    {
        $model = $this->model::findOrFail($this->parameters('payment_channel'));
        $this->validate($request, [
            'name' => "required|unique:payment_channels,name,{$model->id}",
            'payment_methods' => 'required|array|in:' . implode(',', PaymentChannel::METHOD),
            'currency' => 'required|in:' . implode(',', app(\App\Settings\CurrencySetting::class)->types),
            'banks' => 'array',
            'banks.*' => 'exists:banks,id',
            'status' => 'boolean'
        ]);
        $model->update([
            'name' => $request->name,
            'payment_methods' => implode(',', $request->payment_methods),
            'currency' => $request->currency,
            'banks' => implode(',', $request->banks),
            'status' => $request->status,
        ]);

        return $this->response->item($model, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $model = $this->model::findOrFail($this->parameters('payment_channel'));
        $model->delete();

        return $this->success();
    }
}

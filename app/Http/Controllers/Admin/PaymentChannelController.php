<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
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
                AllowedFilter::exact('status'),
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
            'name' => [
                'required',
                Rule::unique('payment_channels')->where(function ($query) use ($request) {
                    return $query->where('currency', $request->get('currency', ''));
                }),
            ],
            'payment_methods' => 'required|array',
            'payment_methods.*' => 'in:' . implode(',', PaymentChannel::METHOD),
            'currency' => 'required|in:' .
                implode(',', array_keys(app(\App\Settings\CurrencySetting::class)->currency)),
            'banks' => 'array',
            'banks.*' => 'exists:banks,id',
            'attributes' => 'required|array',
            'attributes.*' => 'required',
        ]);

        $model = $this->model::create([
            'name' => $request->name,
            'payment_methods' => implode(',', $request->payment_methods),
            'attributes' => $request->get('attributes'),
            'currency' => $request->currency,
            'banks' => implode(',', $request->banks),
        ]);

        return $this->response->item($model, $this->transformer);
    }

    public function update(Request $request)
    {
        $model = $this->model::findOrFail($this->parameters('payment_channel'));
        $this->validate($request, [
            'name' => [
                'required',
                Rule::unique('payment_channels')->where(function ($query) use ($request) {
                    return $query->where('currency', $request->get('currency', ''));
                })->ignore($model->id),
            ],
            'payment_methods' => 'required|array',
            'payment_methods.*' => 'in:' . implode(',', PaymentChannel::METHOD),
            'currency' => 'required|in:' .
                implode(',', array_keys(app(\App\Settings\CurrencySetting::class)->currency)),
            'banks' => 'array',
            'banks.*' => 'exists:banks,id',
            'attributes' => 'required|array',
            'attributes.*' => 'required',
        ]);
        $model->update([
            'name' => $request->name,
            'payment_methods' => implode(',', $request->payment_methods),
            'attributes' => $request->get('attributes'),
            'currency' => $request->currency,
            'banks' => implode(',', $request->banks),
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

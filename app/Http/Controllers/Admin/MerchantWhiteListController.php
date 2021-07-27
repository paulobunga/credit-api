<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class MerchantWhiteListController extends Controller
{
    protected $model = \App\Models\MerchantWhiteList::class;
    protected $transformer = \App\Transformers\Admin\MerchantWhiteListTransformer::class;

    public function index(Request $request)
    {
        $merchant_white_lists = QueryBuilder::for($this->model)
            ->with('merchant')
            ->join('merchants', 'merchants.id', '=', 'merchant_white_lists.merchant_id')
            ->select('merchant_white_lists.*', 'merchants.name')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name', 'merchants.name'),
                AllowedFilter::partial('ip'),
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'merchant_white_lists.id'),
                AllowedSort::field('name', 'merchants.name'),
                'ip',
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($merchant_white_lists, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'merchant_id' => 'required|exists:merchants,id',
            'ip' => [
                'required',
                'ipv4',
                Rule::unique('merchant_white_lists')->where(function ($query) use ($request) {
                    return $query->where([
                        'merchant_id' => $request->merchant_id,
                        'ip' => $request->ip
                    ]);
                }),
            ],
        ]);
        $merchant_white_list = $this->model::create([
            'merchant_id' => $request->merchant_id,
            'ip' => $request->ip,
        ]);

        return $this->response->item($merchant_white_list, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant_white_list = $this->model::findOrFail($this->parameters('merchant_white_list'));
        $this->validate($request, [
            'ip' => [
                'required',
                'ipv4',
                Rule::unique('merchant_white_lists')->where(
                    function ($query) use ($merchant_white_list, $request) {
                        return $query->where([
                            'merchant_id' => $merchant_white_list->id,
                            'ip' => $request->ip
                        ]);
                    }
                ),
            ],
        ]);
        $merchant_white_list->update([
            'ip' => $request->ip,
        ]);

        return $this->response->item($merchant_white_list, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $merchant_white_list = $this->model::findOrFail($this->parameters('merchant_white_list'));
        $merchant_white_list->delete();

        return $this->success();
    }
}

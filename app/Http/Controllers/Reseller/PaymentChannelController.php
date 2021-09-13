<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Dingo\Api\Http\Request;

class PaymentChannelController extends Controller
{
    protected $model = \App\Models\PaymentChannel::class;

    protected $transformer = \App\Transformers\Reseller\PaymentChannelTransformer::class;

    public function index(Request $request)
    {
        $admins = QueryBuilder::for($this->model)
            ->allowedFilters([
                'id',
                AllowedFilter::partial('name'),
                AllowedFilter::exact('currency'),
                AllowedFilter::exact('payin.status', 'payin->status'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'status',
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($admins, $this->transformer);
    }
}

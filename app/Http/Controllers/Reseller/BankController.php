<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class BankController extends Controller
{
    protected $model = \App\Models\Bank::class;
    protected $transformer = \App\Transformers\Reseller\BankTransformer::class;

    public function index(Request $request)
    {
        $banks = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::partial('name', 'banks.name'),
                AllowedFilter::partial('ident'),
                AllowedFilter::exact('status')
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'banks.id'),
                AllowedSort::field('name', 'banks.name'),
                'ident',
                'status'
            ])
            ->where('status', true)
            ->paginate($this->perPage);

        return $this->response->withPaginator($banks, $this->transformer);
    }
}

<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class BankController extends Controller
{
    protected $model = \App\Models\Bank::class;
    protected $transformer = \App\Transformers\Reseller\BankTransformer::class;

    public function index()
    {
        $banks = QueryBuilder::for(
            $this->model::where('status', true)
                ->select('banks.*', 'payment_methods.name as type')
                ->leftjoin('payment_methods', 'banks.payment_method_id', '=', 'payment_methods.id')
                ->filter(
                    request()->get('filter', '{}')
                )->sort(request()->get('sort', 'id'))
        )
            ->allowedFilters([
                'name', 'ident', 'status'
            ])
            ->allowedSorts('id', 'name', 'ident', 'status')
            ->paginate($this->perPage);

        return $this->response->withPaginator($banks, $this->transformer);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use App\Exports\BankExport;

class BankController extends Controller
{
    protected $model = \App\Models\Bank::class;
    protected $transformer = \App\Transformers\Admin\BankTransformer::class;

    public function index(Request $request)
    {
        $banks = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::exact('id'),
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
            ->paginate($this->perPage);

        return $this->response->withPaginator($banks, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'ident' => 'required|unique:banks',
            'name' => 'required',
            'status' => 'boolean',
        ]);
        $bank = $this->model::create([
            'ident' => $request->ident,
            'name' => $request->name,
            'status' => $request->status
        ]);

        return $this->response->item($bank, $this->transformer);
    }

    public function update(Request $request)
    {
        $bank = $this->model::findOrFail($this->parameters('bank'));
        $this->validate($request, [
            'ident' => "required|unique:banks,ident,{$bank->id}",
            'name' => 'required',
            'status' => 'boolean',
        ]);

        $bank->update([
            'ident' => $request->ident,
            'name' => $request->name,
            'status' => $request->status
        ]);

        return $this->response->item($bank, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $bank = $this->model::findOrFail($this->parameters('bank'));
        $bank->delete();

        return $this->success();
    }

    public function export()
    {
        return new BankExport(
            QueryBuilder::for($this->model)
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
                ])->get()
        );
    }
}

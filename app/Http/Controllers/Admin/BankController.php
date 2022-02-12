<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Controller of CRUD bank
 * @package App\Http\Controllers\Admin
 */
class BankController extends Controller
{
    protected $model = \App\Models\Bank::class;

    protected $transformer = \App\Transformers\Admin\BankTransformer::class;

    /**
     * Get bank list
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $banks = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('ident'),
                AllowedFilter::exact('currency'),
                AllowedFilter::exact('status')
            ])
            ->allowedSorts([
                'id',
                'name',
                'currency',
                'ident',
                'status'
            ]);

        return $this->paginate($banks, $this->transformer);
    }

    /**
     * Create a bank
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'ident' => 'required|unique:banks',
            'name' => 'required',
            'currency' => 'required|currency',
            'status' => 'boolean',
        ]);
        $bank = $this->model::create([
            'ident' => $request->ident,
            'name' => $request->name,
            'currency' => $request->currency,
            'status' => $request->status,
        ]);

        return $this->response->item($bank, $this->transformer);
    }

    /**
     * Update given bank via bank id
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $bank = $this->model::findOrFail($this->parameters('bank'));
        $this->validate($request, [
            'ident' => "required|unique:banks,ident,{$bank->id}",
            'name' => 'required',
            'currency' => 'required|currency',
            'status' => 'boolean',
        ]);

        $bank->update([
            'ident' => $request->ident,
            'name' => $request->name,
            'currency' => $request->currency,
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
}

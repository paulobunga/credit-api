<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BankController extends Controller
{
    protected $model = \App\Models\Bank::class;
    protected $transformer = \App\Transformers\BankTransformer::class;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function index()
    {
        $banks = QueryBuilder::for($this->model)
            ->allowedFilters([
                'name',
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($banks, $this->transformer);
    }

    public function create(Request $request)
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

    public function destroy(String $id)
    {
        $bank = $this->model::findOrFail($id);
        $bank->delete();
        return $this->success();
    }

    public function edit(Request $request, String $id)
    {
        $bank = $this->model::findOrFail($id);
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
        return $this->success();
    }
}

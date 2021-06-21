<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerBankCardController extends Controller
{
    protected $model = \App\Models\ResellerBankCard::class;
    protected $transformer = \App\Transformers\ResellerBankCardTransformer::class;

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
                'id',
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($banks, $this->transformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'reseller' => 'required|exists:resellers,id',
            'bank_id' => "required|exists:banks,id",
            'type' => 'numeric|between:0,2',
            'account_name' => 'required',
            'account_no' => 'numeric',
            'status' => 'boolean',
        ]);
        $bank = $this->model::create([
            'reseller_id' => $request->reseller,
            'bank_id' => $request->bank_id,
            'type' => $request->type,
            'account_name' => $request->account_name,
            'account_no' => $request->account_no,
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
        $reseller_bank_card = $this->model::findOrFail($id);
        $this->validate($request, [
            'bank_id' => "required|exists:banks,id",
            'type' => 'numeric|between:0,2',
            'account_name' => 'required',
            'account_no' => 'numeric',
            'status' => 'boolean',
        ]);

        $reseller_bank_card->update([
            'bank_id' => $request->bank_id,
            'type' => $request->type,
            'account_no' => $request->account_no,
            'account_name' => $request->account_name,
            'status' => $request->status
        ]);
        return $this->success();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ResellerBankCardController extends Controller
{
    protected $model = \App\Models\ResellerBankCard::class;
    protected $transformer = \App\Transformers\Admin\ResellerBankCardTransformer::class;

    public function index()
    {
        $reseller_bank_card = QueryBuilder::for($this->model)
            ->allowedFilters([
                'name',
                'id',
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($reseller_bank_card, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'reseller' => 'required|exists:resellers,id',
            'bank_id' => "required|exists:banks,id",
            'type' => 'numeric|between:0,2',
            'account_name' => 'required',
            'account_no' => 'required|numeric',
            'status' => 'required|boolean',
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

    public function update(Request $request)
    {
        $reseller_bank_card = $this->model::findOrFail($this->parameters('reseller_bank_card'));
        $this->validate($request, [
            'bank_id' => "required|exists:banks,id",
            'type' => 'numeric|between:0,2',
            'account_name' => 'required',
            'account_no' => 'required|numeric',
            'status' => 'required|boolean',
        ]);

        $reseller_bank_card->update([
            'bank_id' => $request->bank_id,
            'type' => $request->type,
            'account_no' => $request->account_no,
            'account_name' => $request->account_name,
            'status' => $request->status
        ]);

        return $this->response->item($reseller_bank_card, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $bank = $this->model::findOrFail($this->parameters('reseller_bank_card'));
        $bank->delete();

        return $this->success();
    }
}

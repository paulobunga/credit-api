<?php

namespace App\Http\Controllers\Merchant;

use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SettlementController extends Controller
{
    protected $model = \App\Models\MerchantSettlement::class;

    protected $transformer = \App\Transformers\Merchant\SettlementTransformer::class;

    public function index(Request $request)
    {
        $withdrawals = QueryBuilder::for($this->model::where('merchant_id', auth()->id()))
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($withdrawals, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|max:' . auth()->user()->credit,
        ]);
        DB::beginTransaction();
        try {
            $withdrawal = $this->model::create([
                'merchant_id' => auth()->id(),
                'amount' => $request->amount,
                'status' => 0,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return $this->response->item($withdrawal, $this->transformer);
    }
}

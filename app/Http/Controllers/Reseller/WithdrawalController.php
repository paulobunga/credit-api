<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    protected $model = \App\Models\ResellerWithdrawal::class;
    protected $transformer = \App\Transformers\Reseller\WithdrawalTransformer::class;

    public function index(Request $request)
    {
        $withdrawals = QueryBuilder::for($this->model::where('reseller_id', Auth::id()))
            ->allowedFilters([
                'id'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($withdrawals, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:1|max:' . Auth::user()->coin,
        ]);
        DB::beginTransaction();
        try {
            $last_order_id = $this->model::latest()->first()->id?? 0;
            $last_order_id += 1;
            $withdrawal = $this->model::create([
                'order_id' => '#' . str_pad($last_order_id + 1, 8, "0", STR_PAD_LEFT) . time(),
                'reseller_id' => Auth::id(),
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

<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    protected $model = \App\Models\MerchantWithdrawal::class;
    protected $transformer = \App\Transformers\Merchant\WithdrawalTransformer::class;

    public function index(Request $request)
    {
        $withdrawals = QueryBuilder::for($this->model::where('merchant_id', Auth::id()))
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($withdrawals, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|max:' . Auth::user()->credit,
        ]);
        DB::beginTransaction();
        try {
            $last_order = $this->model::latest()->first();
            $withdrawal = $this->model::create([
                'order_id' => '#' . str_pad($last_order->id + 1, 8, "0", STR_PAD_LEFT) . time(),
                'merchant_id' => Auth::id(),
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

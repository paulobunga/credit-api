<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\MerchantWithdrawal;

class WithdrawalController extends Controller
{
    protected $model = MerchantWithdrawal::class;

    protected $transformer = \App\Transformers\Reseller\WithdrawalTransformer::class;

    public function index(Request $request)
    {
        $withdrawals = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::callback(
                    'status',
                    function (Builder $query, $v) {
                        if (is_array($v)) {
                            $query->whereIn('status', $v);
                        } else {
                            $query->where('status', $v);
                        }
                    }
                ),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('created_at', $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'order_id',
                'amount',
                'status',
                'created_at',
            ])
            ->where('reseller_id', auth()->id())
            ->paginate($this->perPage);

        return $this->response->withPaginator($withdrawals, $this->transformer);
    }

    public function update(Request $request)
    {
        $withdrawal = $this->model::findOrFail($this->parameters('withdrawal'));
        if ($withdrawal->reseller_id != auth()->id()) {
            throw new \Exception('Unauthorize', 401);
        }
        if (!in_array($withdrawal->status, [
            MerchantWithdrawal::STATUS['PENDING']
        ])) {
            throw new \Exception('Status is not allowed to update', 401);
        }
        $this->validate($request, [
            'status' => 'required|numeric|in:' . implode(',', [
                MerchantWithdrawal::STATUS['FINISHED'],
                MerchantWithdrawal::STATUS['REJECTED'],
            ]),
            'slip' => [
                'required_if:status,' . MerchantWithdrawal::STATUS['FINISHED'],
                'image',
            ]
        ]);
        if (Storage::disk('s3')->exists("withdrawals/$withdrawal->order_id")) {
            throw new \Exception('Slip is already exists!', 405);
        }
        $request->file('slip')->storeAs(
            'withdrawals',
            $withdrawal->order_id,
            's3'
        );
        $withdrawal->update([
            'status' => $request->status,
        ]);

        return $this->response->item($withdrawal, $this->transformer);
    }
}

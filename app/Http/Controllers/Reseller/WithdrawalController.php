<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\MerchantWithdrawal;
use App\Filters\DateFilter;

class WithdrawalController extends Controller
{
    protected $model = MerchantWithdrawal::class;

    protected $transformer = \App\Transformers\Reseller\WithdrawalTransformer::class;

    public function index(Request $request)
    {
        $withdrawals = QueryBuilder::for($this->model)
            ->with([
                'transactions',
                'reseller',
            ])
            ->allowedFilters([
                AllowedFilter::partial('merchant_order_id'),
                AllowedFilter::partial('amount'),
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
                AllowedFilter::custom('created_at_between', new DateFilter('merchant_withdrawals')),
            ])
            ->allowedSorts([
                'id',
                'merchant_order_id',
                'amount',
                'status',
                'created_at',
            ])
            ->where('reseller_id', auth()->id());

        return $this->paginate($withdrawals, $this->transformer);
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
            ],
            'reference_id' => 'required_if:status,' . MerchantWithdrawal::STATUS['FINISHED'],
        ]);
        if ($request->status == MerchantWithdrawal::STATUS['FINISHED']) {
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
                'extra' => $withdrawal->extra + ['reference_id' => $request->reference_id]
            ]);
        } else {
            $withdrawal->update([
                'status' => $request->status,
            ]);
        }

        return $this->response->item($withdrawal, $this->transformer);
    }

    /**
     * Get slip url of withdrawal
     *
     * @method GET
     *
     * @return array
     */
    public function slip()
    {
        $withdrawal = $this->model::findOrFail($this->parameters('withdrawal'));
        if ($withdrawal->reseller_id != auth()->id()) {
            throw new \Exception('Unauthorize', 401);
        }
        if (!in_array($withdrawal->status, [
            MerchantWithdrawal::STATUS['FINISHED'],
            MerchantWithdrawal::STATUS['APPROVED'],
            MerchantWithdrawal::STATUS['CANCELED']
        ])) {
            throw new \Exception('Status is invalid', 401);
        }

        return response()->json([
            'message' => 'success',
            'data' => [
                'url' => $withdrawal->slipUrl
            ]
        ]);
    }
}

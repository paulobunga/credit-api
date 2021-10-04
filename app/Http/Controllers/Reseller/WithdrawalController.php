<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
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
}

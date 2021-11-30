<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ReportDailyReseller;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    protected $model = ReportDailyReseller::class;

    protected $transformer = \App\Transformers\Reseller\ReportTransformer::class;
    /**
     * Get lists of agent report
     *
     * @param  \Dingo\Api\Http\Request $request
     * @return json
     */
    public function index(Request $request)
    {
        $reports = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::callback(
                    'date_between',
                    fn ($query, $v) => $query->where([
                        ['start_at', '>=', $v[0]],
                        ['end_at', '<=', $v[1]],
                    ])
                ),
            ])
            ->allowedSorts([
                'id',
                'start_at',
                'end_at',
                'turnover',
                'credit',
                'coin',
                'amount',
                'currency',
            ])
            ->where('reseller_id', auth()->id());

        return $this->paginate($reports, $this->transformer);
    }
}

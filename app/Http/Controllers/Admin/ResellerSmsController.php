<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerSms;
use App\Filters\Admin\ResellerSmsCreatedAtBetweenFilter;

class ResellerSmsController extends Controller
{
    protected $model = ResellerSms::class;

    protected $transformer = \App\Transformers\Admin\ResellerSmsTransformer::class;

    /**
     * Get SMS list
     *
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $m = QueryBuilder::for($this->model)
            ->with([
                'reseller'
            ])
            ->join('resellers', 'resellers.id', '=', 'reseller_sms.reseller_id')
            ->select(
                'reseller_sms.*',
                'resellers.name'
            )
            ->allowedFilters([
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('created_at_between', new ResellerSmsCreatedAtBetweenFilter),
            ])
            ->allowedSorts([
                'id',
                'status'
            ]);

        return $this->paginate($m, $this->transformer);
    }
}

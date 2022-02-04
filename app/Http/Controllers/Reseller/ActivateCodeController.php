<?php

namespace App\Http\Controllers\Reseller;

use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use App\Models\ResellerActivateCode;
use App\Http\Controllers\Controller;

class ActivateCodeController extends Controller
{
    protected $model = ResellerActivateCode::class;

    protected $transformer = \App\Transformers\Reseller\ActivateCodeTransformer::class;

    /**
     * Get activate code list
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $codes = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::partial('name', 'banks.name'),
                AllowedFilter::partial('ident'),
                AllowedFilter::exact('status')
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'banks.id'),
                AllowedSort::field('name', 'banks.name'),
                'ident',
                'status'
            ])
            ->where('reseller_id', auth()->id())
            ->paginate($this->perPage);

        return $this->response->withPaginator($codes, $this->transformer);
    }

    /**
     * Create activate code
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method POST
     * @return json
     */
    public function store(Request $request)
    {
        $pending_codes = ResellerActivateCode::where(
            'reseller_id',
            auth()->id()
        )->whereIn('status', [
            ResellerActivateCode::STATUS['PENDING'],
        ])->count();

        if ($pending_codes + auth()->user()->downline > auth()->user()->downline_slot) {
            throw new \Exception('Exceed downline limit!');
        }

        $code = $this->model::create([
            'reseller_id' => auth()->id(),
            'status' => ResellerActivateCode::STATUS['PENDING'],
            'expired_at' => Carbon::now()->addMinutes(600)
        ]);

        return $this->response->item($code, $this->transformer);
    }
}

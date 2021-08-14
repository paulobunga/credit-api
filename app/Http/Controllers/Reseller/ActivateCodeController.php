<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Illuminate\Support\Facades\Auth;
use App\Models\ResellerActivateCode;
use Carbon\Carbon;

class ActivateCodeController extends Controller
{
    protected $model = ResellerActivateCode::class;
    protected $transformer = \App\Transformers\Reseller\ActivateCodeTransformer::class;

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
            ->where('reseller_id', Auth::id())
            ->paginate($this->perPage);

        return $this->response->withPaginator($codes, $this->transformer);
    }

    public function store(Request $request)
    {
        $pendings = ResellerActivateCode::where(
            'reseller_id',
            Auth::id()
        )->whereIn('status', [
            ResellerActivateCode::STATUS['PENDING'],
            ResellerActivateCode::STATUS['ACTIVATED'],
        ])->count();

        if ($pendings >= Auth::user()->downline_slot) {
            throw new \Exception('Exceed downline limit');
        }

        $code = $this->model::create([
            'reseller_id' => Auth::id(),
            'status' => ResellerActivateCode::STATUS['PENDING'],
            'expired_at' => Carbon::now()->addMinutes(1)
        ]);

        return $this->response->item($code, $this->transformer);
    }
}

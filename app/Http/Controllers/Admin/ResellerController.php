<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class ResellerController extends Controller
{
    protected $model = \App\Models\Reseller::class;
    protected $transformer = \App\Transformers\Admin\ResellerTransformer::class;

    public function index(Request $request)
    {
        $resellers = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('level'),
                AllowedFilter::exact('status')
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'id'),
                AllowedSort::field('name', 'name'),
                'level',
                'status'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($resellers, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'level' => 'required|between:0,3',
            'upline' => 'required_unless:level,0',
            'name' => 'required|unique:resellers,name',
            'username' => 'required|unique:resellers,username',
            'phone' => 'required|unique:resellers,phone',
            'password' => 'required|confirmed',
            'commission_percentage' => 'required|numeric',
            'pending_limit' => 'required|numeric',
            'downline_slot' => 'required|numeric',
            // 'status' => 'required|boolean'
        ]);
        $reseller = $this->model::create([
            'level' => $request->level,
            'upline' => $request->get('upline', 0),
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'password' => $request->password,
            'commission_percentage' => $request->commission_percentage,
            'pending_limit' => $request->pending_limit,
            'downline_slot' => $request->downline_slot,
            'status' => $request->level > 2 ? 0 : 1,
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller = $this->model::where('name', $this->parameters('reseller'))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:resellers,name,{$reseller->id}",
            'username' => "required|unique:resellers,username,{$reseller->id}",
            'phone' => "required|unique:resellers,phone,{$reseller->id}",
            'commission_percentage' => 'required|numeric',
            'pending_limit' => 'required|numeric',
            'status' => 'required|boolean'
        ]);
        $reseller->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'commission_percentage' => $request->commission_percentage,
            'pending_limit' => $request->pending_limit,
            'status' => $request->status,
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $reseller = $this->model::where('name', $this->parameters('reseller'))->firstOrFail();
        $reseller->delete();

        return $this->success();
    }

    public function deposit(Request $request)
    {
        $reseller = $this->model::findOrFail($this->parameters('reseller'));
        $this->validate($request, [
            'credit' => 'required|numeric',
        ]);
        $reseller->increment('credit', $request->credit);

        return $this->response->item($reseller, $this->transformer);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use App\Models\Reseller;

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
                'level',
                AllowedSort::field('name', 'name'),
                'username',
                'phone',
                'credit',
                'coin',
                'pending_limit',
                'commission_percentage',
                'downline_slot',
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
            'phone' => 'required',
            'password' => 'required|confirmed',
        ]);
        $commission_setting = app(\App\Settings\CommissionSetting::class);
        $reseller_setting = app(\App\Settings\ResellerSetting::class);
        $agent_setting = app(\App\Settings\AgentSetting::class);

        $reseller = $this->model::create([
            'level' => $request->level,
            'upline' => $request->get('upline', 0),
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'password' => $request->password,
            'commission_percentage' => $commission_setting->getDefaultPercentage($request->level),
            'pending_limit' => $reseller_setting->getDefaultPendingLimit($request->level),
            'downline_slot' => $agent_setting->getDefaultDownLineSlot($request->level),
            'status' => ($request->level == Reseller::LEVEL['RESELLER']) ?
                Reseller::STATUS['INACTIVE'] :
                Reseller::STATUS['ACTIVE'],
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    public function update(Request $request)
    {
        $reseller = $this->model::where('name', $this->parameters('reseller'))->firstOrFail();
        $this->validate($request, [
            'level' => 'required',
            'name' => "required|unique:resellers,name,{$reseller->id}",
            'username' => "required|unique:resellers,username,{$reseller->id}",
            'phone' => "required",
            'commission_percentage' => 'required|numeric',
            'pending_limit' =>
            'required|numeric|max:' . app(\App\Settings\ResellerSetting::class)->max_pending_limit,
            'downline_slot' =>
            'required_with:level,1,2|numeric|max:' . app(\App\Settings\AgentSetting::class)->max_downline_slot
        ]);
        $reseller->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'commission_percentage' => $request->commission_percentage,
            'pending_limit' => $request->pending_limit,
            'downline_slot' => in_array($request->level, [1, 2]) ? $request->downline_slot : 0,
        ]);

        return $this->response->item($reseller, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $reseller = $this->model::where('name', $this->parameters('reseller'))->firstOrFail();
        if ($reseller->id == 1) {
            throw new \Exception('Default referrer cannot be removed!', 405);
        }
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

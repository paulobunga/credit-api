<?php

namespace App\Http\Controllers\Admin;

use Dingo\Api\Http\Request;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{
    protected $model = \App\Models\Team::class;

    protected $transformer = \App\Transformers\Admin\TeamTransformer::class;

    /**
     * Get team list
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $m = QueryBuilder::for($this->model)
            ->with(['agents', 'merchants'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('currency'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'type',
                'currency',
            ]);

        return $this->paginate($m, $this->transformer);
    }

    /**
     * Get team genre
     * @method GET
     * @return json
     */
    public function genre()
    {
        return [
            'data' => array_values($this->model::TYPE)
        ];
    }

    /**
     * Show team by id
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function show(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('team'));

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Create a team
     * @param \Dingo\Api\Http\Request $request
     * @method POST
     * @return json
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required|in:' .
                implode(',', $this->model::TYPE),
            'currency' => 'required|in:' .
                implode(',', array_keys(app('settings.currency')->currency)),
            'description' => 'required',
        ]);

        $count = $this->model::where([
            'name' => $request->name,
            'type' => $request->type,
            'currency' => $request->currency,
        ])->count();
        if ($count) {
            throw new \Exception('Duplicate Team is found');
        }

        $m = $this->model::create([
            'name' => $request->name,
            'type' => $request->type,
            'currency' => $request->currency,
            'description' => $request->description
        ]);

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Update team by id
     * @param \Dingo\Api\Http\Request $request
     * @method PUT
     * @return json
     */
    public function update(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('team'));
        $this->validate($request, [
            'name' => "required",
            'description' => 'required',
        ]);

        $count = $this->model::where([
            'type' => $m->type,
            'name' => $request->name,
            'currency' => $m->currency,
            ['id', '!=', $m->id]
        ])->count();
        if ($count) {
            throw new \Exception('Duplicate Team is found');
        }

        $m->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Assign member by id
     * @param \Dingo\Api\Http\Request $request
     * @method PUT
     * @return json
     */
    public function member(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('team'));
        $this->validate($request, [
            'model_type' => "required|in:agent,merchant",
            'values' => 'array',
            'values.*.id' => 'required',
            'values.*.value' => 'required',
        ]);
        $values = Arr::pluck($request->values, 'value', 'id');
        switch ($request->model_type) {
            case 'agent':
                $agents = \App\Models\Reseller::with('teams')->whereIn('id', array_keys($values))->get();
                foreach ($agents as $agent) {
                    if ($values[$agent->id]) {
                        // $teams = $agent->teams()->where('type', '!=', $m->type)->pluck('id')->toArray();
                        // $teams[] = $m->id;
                        // $agent->syncTeams(...$teams);
                        $agent->assignTeams($m->id);
                    } else {
                        $agent->removeTeam($m->id);
                    }
                }
                break;
            case 'merchant':
                $merchants = \App\Models\Merchant::whereIn('id', array_keys($values))->get();
                foreach ($merchants as $merchant) {
                    if ($values[$merchant->id]) {
                        $merchant->assignTeams($m->id);
                    } else {
                        $merchant->removeTeam($m->id);
                    }
                }
                break;
        }

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Delete team by id
     * @param \Dingo\Api\Http\Request $request
     * @method DELETE
     * @return json
     */
    public function destroy(Request $request)
    {
        $m = $this->model::findOrFail($this->parameters('team'));
        if ($m->name == 'Default') {
            throw new \Exception('Default team cannot be removed!', 403);
        }
        $m->delete();

        return $this->success();
    }
}

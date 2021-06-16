<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends Controller
{
    protected $model = \App\Models\Role::class;
    protected $transformer = \App\Transformers\RoleTransformer::class;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function index()
    {
        $roles = QueryBuilder::for($this->model)
            ->allowedFilters(['name'])
            ->paginate($this->perPage);
        return $this->response->withPaginator($roles, $this->transformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $role = $this->model::create([
            'name' => $request->name
        ]);
        return $this->response->item($role, $this->transformer);
    }

    public function destroy(String $name)
    {
        $role = $this->model::where('name', $name)->firstOrFail();
        $role->delete();
        return [
            'message' => 'success'
        ];
    }

    public function edit(Request $request, String $name)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles'
        ]);
        $role = $this->model::where('name', $name)->firstOrFail();
        $role->update([
            'name' => $request->name
        ]);
        return [
            'message' => 'success'
        ];
    }
}

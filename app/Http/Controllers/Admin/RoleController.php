<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends Controller
{
    protected $model = \App\Models\Role::class;
    
    protected $transformer = \App\Transformers\Admin\RoleTransformer::class;

    public function index(Request $request)
    {
        $roles = QueryBuilder::for($this->model)
            ->allowedFilters(['name'])
            ->paginate($this->perPage);

        return $this->response->withPaginator($roles, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'permissions' => 'array',
            'permissions.*' => 'required|string|exists:permissions,name'
        ]);

        $role = $this->model::create([
            'name' => $request->name
        ]);
        $role->syncPermissions($request->permissions);

        return $this->response->item($role, $this->transformer);
    }

    public function update(Request $request)
    {
        $role = $this->model::findOrFail($this->parameters('role'));
        $this->validate($request, [
            'name' => "required|unique:roles,name,{$role->id}",
            'permissions' => 'array',
            'permissions.*' => 'required|string|exists:permissions,name'
        ]);

        $role->update([
            'name' => $request->name
        ]);
        $role->syncPermissions($request->permissions);

        return $this->response->item($role, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $role = $this->model::findOrFail($this->parameters('role'));
        $role->delete();

        return [
            'message' => 'success'
        ];
    }
}

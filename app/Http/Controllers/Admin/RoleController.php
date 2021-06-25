<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends Controller
{
    protected $model = \App\Models\Role::class;
    protected $transformer = \App\Transformers\Admin\RoleTransformer::class;

    public function index()
    {
        $roles = QueryBuilder::for($this->model)
            ->allowedFilters(['name'])
            ->paginate($this->perPage);

        return $this->response->withPaginator($roles, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $role = $this->model::create([
            'name' => $request->name
        ]);

        return $this->response->item($role, $this->transformer);
    }

    public function update(Request $request)
    {
        $role = $this->model::where('name', urldecode($this->parameters('role')))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:roles,name,{$role->id}"
        ]);

        $role->update([
            'name' => $request->name
        ]);

        return $this->response->item($role, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $role = $this->model::where('name', urldecode($this->parameters('role')))->firstOrFail();
        $role->delete();

        return [
            'message' => 'success'
        ];
    }
}

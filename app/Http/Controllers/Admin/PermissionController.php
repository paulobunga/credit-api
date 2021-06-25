<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionController extends Controller
{
    protected $model = \App\Models\Permission::class;
    protected $transformer = \App\Transformers\Admin\PermissionTransformer::class;

    public function index()
    {
        $permissions = QueryBuilder::for($this->model)
            ->allowedFilters(['name'])
            ->paginate($this->perPage);

        return $this->response->withPaginator($permissions, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $permission = $this->model::create([
            'name' => $request->name
        ]);

        return $this->response->item($permission, $this->transformer);
    }

    public function update(Request $request)
    {
        $permission = $this->model::where('name', urldecode($this->parameters('permission')))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:permissions,name,{$permission->id}"
        ]);
        $permission->update([
            'name' => $request->name
        ]);

        return $this->response->item($permission, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $permission = $this->model::where('name', urldecode($this->parameters('permission')))->firstOrFail();
        $permission->delete();

        return $this->success();
    }
}

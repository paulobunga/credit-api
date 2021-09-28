<?php

namespace App\Http\Controllers\Admin;

use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    protected $model = \App\Models\Permission::class;
    
    protected $transformer = \App\Transformers\Admin\PermissionTransformer::class;

    public function index(Request $request)
    {
        $permissions = $this->model::all();

        return $this->response->collection($permissions, $this->transformer);
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
        $permission = $this->model::findOrFail($this->parameters('permission'));
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
        $permission = $this->model::findOrFail($this->parameters('permission'));
        $permission->delete();

        return $this->success();
    }
}

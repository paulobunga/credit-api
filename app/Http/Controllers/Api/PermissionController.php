<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionController extends Controller
{
    protected $model = \App\Models\Permission::class;
    protected $transformer = \App\Transformers\PermissionTransformer::class;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function index()
    {
        $permissions = QueryBuilder::for($this->model)
            ->allowedFilters(['name'])
            ->paginate($this->perPage);
        return $this->response->withPaginator($permissions, $this->transformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $permission = $this->model::create([
            'name' => $request->name
        ]);
        return $this->response->item($permission, $this->transformer);
    }

    public function destroy(String $name)
    {
        $permission = $this->model::where('name', $name)->firstOrFail();
        $permission->delete();
        return [
            'message' => 'success'
        ];
    }

    public function edit(Request $request, String $name)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions'
        ]);
        $permission = $this->model::where('name', $name)->firstOrFail();
        $permission->update([
            'name' => $request->name
        ]);
        return [
            'message' => 'success'
        ];
    }
}
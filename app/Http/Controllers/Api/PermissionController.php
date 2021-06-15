<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Transformers\PermissionTransformer;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('api.auth');
    }

    public function index()
    {
        $permissions = QueryBuilder::for(Permission::class)
                        ->allowedFilters(['name'])
                        ->paginate($this->perPage);
        return $this->response->withPaginator($permissions, new PermissionTransformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $permission = Permission::create([
            'name' => $request->name
        ]);
        return $this->response->item($permission, new PermissionTransformer);
    }

    public function destroy(String $name)
    {
        $permission = Permission::where('name', $name)->firstOrFail();
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
        $permission = Permission::where('name', $name)->firstOrFail();
        $permission->update([
            'name' => $request->name
        ]);
        return [
            'message' => 'success'
        ];
    }
}

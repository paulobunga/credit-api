<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Dingo\Api\Http\Request;

class AdminController extends Controller
{
    protected $model = \App\Models\Admin::class;

    protected $transformer = \App\Transformers\Admin\AdminTransformer::class;

    /**
     * Get admininstrator lists
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $admins = QueryBuilder::for($this->model)
            ->with([
                'roles'
            ])
            ->join('model_has_roles', function ($join) {
                $join->on('admins.id', '=', 'model_has_roles.model_id')
                 ->where('model_has_roles.model_type', '=', 'admin');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('admins.*', 'roles.name AS role')
            ->allowedFilters([
                'id',
                AllowedFilter::partial('name'),
                AllowedFilter::partial('role', 'roles.name'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'username',
                'status',
                'role',
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($admins, $this->transformer);
    }

    /**
     * Create an administrator
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:admins,name',
            'username' => 'required|unique:admins,username',
            'role' => 'required',
            'password' => 'required|confirmed',
            'status' => 'boolean'
        ]);

        $role = \App\Models\Role::findOrFail($request->role);

        $admin = $this->model::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password,
            'status' => $request->status,
        ]);
        $admin->syncRoles($role);

        return $this->response->item($admin, $this->transformer);
    }

    /**
     * Update an administrator via name
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $admin = $this->model::findOrFail($this->parameters('admin'));
        if ($admin->id == 1) {
            throw new \Exception('Default Administrator cannot be edited!', 405);
        }
        $this->validate($request, [
            'name' => "required|unique:admins,name,{$admin->id}",
            'username' => "required|unique:admins,username,{$admin->id}",
            'role' => 'required',
            'status' => 'boolean'
        ]);
        $role = \App\Models\Role::findOrFail($request->role);
        $admin->update([
            'name' => $request->name,
            'username' => $request->username,
            'status' => $request->status,
        ]);
        $admin->syncRoles($role);

        return $this->response->item($admin, $this->transformer);
    }

    /**
     * Delete an administrator via id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $admin = $this->model::findOrFail($this->parameters('admin'));
        if ($admin->id == 1) {
            throw new \Exception('Default Administrator cannot be deleted!', 405);
        }
        $admin->delete();

        return $this->success();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Dingo\Api\Http\Request;

class PaymentChannelController extends Controller
{
    protected $model = \App\Models\PaymentChannel::class;

    protected $transformer = \App\Transformers\Admin\PaymentChannelTransformer::class;

    public function index(Request $request)
    {
        $admins = QueryBuilder::for($this->model)
            ->allowedFilters([
                'id',
                AllowedFilter::partial('name'),
                AllowedFilter::exact('currency'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'status',
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($admins, $this->transformer);
    }

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

    public function update(Request $request)
    {
        $admin = $this->model::where('name', $this->parameters('admin'))->firstOrFail();
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

    public function destroy(Request $request)
    {
        $admin = $this->model::where('name', $this->parameters('admin'))->firstOrFail();
        if ($admin->id == 1) {
            throw new \Exception('Default Administrator cannot be removed!', 405);
        }
        $admin->delete();

        return $this->success();
    }
}

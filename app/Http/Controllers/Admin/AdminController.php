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

    public function index(Request $request)
    {
        $admins = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::custom('name', new \App\Http\Filters\AdminFilter),
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($admins, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:admins,name',
            'username' => 'required|unique:admins,username',
            'password' => 'required|confirmed',
            'status' => 'boolean'
        ]);

        $admin = $this->model::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password,
            'status' => $request->status,
        ]);

        return $this->response->item($admin, $this->transformer);
    }

    public function update(Request $request)
    {
        $admin = $this->model::where('name', $this->parameters('admin'))->firstOrFail();
        $this->validate($request, [
            'name' => "required|unique:admins,name,{$admin->id}",
            'username' => "required|unique:admins,username,{$admin->id}",
            'status' => 'boolean'
        ]);
        $admin->update([
            'name' => $request->name,
            'username' => $request->username,
            'status' => $request->status,
        ]);

        return $this->response->item($admin, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $admin = $this->model::where('name', $this->parameters('admin'))->firstOrFail();
        $admin->delete();

        return $this->success();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminController extends Controller
{
    protected $model = \App\Models\Admin::class;
    protected $transformer = \App\Transformers\AdminTransformer::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $admins = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::custom('name', new \App\Http\Filters\AdminFilter),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($admins, $this->transformer);
    }

    public function create(Request $request)
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

    public function destroy(String $name)
    {
        $admin = $this->model::where('name', $name)->firstOrFail();
        $admin->delete();
        return $this->success();
    }

    public function edit(Request $request, String $name)
    {
        $admin = $this->model::where('name', $name)->firstOrFail();
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
        return $this->success();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;
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
        $this->middleware('api.auth');
    }

    public function index()
    {
        $admins = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::custom('name', new \App\Http\Filters\AdminFilter)
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($admins, $this->transformer);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'username' => 'required',
            'password' => 'required'
        ]);
        $admin = $this->model::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password
        ]);
        return $this->response->item($admin, $this->transformer);
    }

    public function destroy(String $name)
    {
        $admin = $this->model::where('name', $name)->firstOrFail();
        $admin->delete();
        return [
            'message' => 'success'
        ];
    }

    public function edit(Request $request, String $name)
    {
        $this->validate($request, [
            'name' => 'required|unique:admins'
        ]);
        $admin = $this->model::where('name', $name)->firstOrFail();
        $admin->update([
            'name' => $request->name
        ]);
        return [
            'message' => 'success'
        ];
    }
}

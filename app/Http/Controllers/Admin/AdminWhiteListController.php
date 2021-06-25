<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminWhiteListController extends Controller
{
    protected $model = \App\Models\AdminWhiteList::class;
    protected $transformer = \App\Transformers\Admin\AdminWhiteListTransformer::class;

    public function index()
    {
        $admin_white_lists = QueryBuilder::for($this->model::with('admin'))
            ->allowedFilters([
                'id',
                AllowedFilter::custom('value', new \App\Http\Filters\AdminWhiteListFilter)
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($admin_white_lists, $this->transformer);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'ip' => [
                'required',
                'ipv4',
                Rule::unique('admin_white_lists')->where(function ($query) use ($request) {
                    return $query->where([
                        'admin_id' => $request->admin_id,
                        'ip' => $request->ip
                    ]);
                }),
            ],
            'status' => 'boolean',
        ]);
        $admin_white_list = $this->model::create([
            'admin_id' => $request->admin_id,
            'ip' => $request->ip,
            'status' => $request->status,
        ]);

        return $this->response->item($admin_white_list, $this->transformer);
    }

    public function update(Request $request)
    {
        $admin_white_list = $this->model::findOrFail($this->parameters('admin_white_list'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'ip' => [
                'required',
                'ipv4',
                Rule::unique('admin_white_lists')->where(function ($query) use ($request) {
                    return $query->where([
                        'admin_id' => $request->admin_id,
                        'ip' => $request->ip
                    ]);
                }),
            ],
            'status' => 'boolean'
        ]);
        $admin_white_list->update([
            'admin_id' => $request->admin_id,
            'ip' => $request->ip,
            'status' => $request->status
        ]);

        return $this->response->item($admin_white_list, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $admin_white_list = $this->model::findOrFail($this->parameters('admin_white_list'));
        $admin_white_list->delete();

        return $this->success();
    }
}

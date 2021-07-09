<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class AdminWhiteListController extends Controller
{
    protected $model = \App\Models\AdminWhiteList::class;
    protected $transformer = \App\Transformers\Admin\AdminWhiteListTransformer::class;

    public function index(Request $request)
    {
        $admin_white_lists = QueryBuilder::for(
            $this->model::join('admins', 'admin_white_lists.admin_id', '=', 'admins.id')
                ->select('admin_white_lists.*')
                ->sort(request()->get('sort', 'id'))
        )
            ->allowedFilters(
                'id',
                AllowedFilter::custom('value', new \App\Http\Filters\AdminWhiteListFilter)
            )
            ->allowedSorts('id', 'ip', 'name')
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
        ]);
        $admin_white_list = $this->model::create([
            'admin_id' => $request->admin_id,
            'ip' => $request->ip,
        ]);

        return $this->response->item($admin_white_list, $this->transformer);
    }

    public function update(Request $request)
    {
        $admin_white_list = $this->model::findOrFail($this->parameters('admin_white_list'));
        $this->validate($request, [
            'ip' => [
                'required',
                'ipv4',
                Rule::unique('admin_white_lists')->where(
                    function ($query) use ($admin_white_list, $request) {
                        return $query->where([
                            'admin_id' => $admin_white_list->id,
                            'ip' => $request->ip
                        ]);
                    }
                ),
            ],
        ]);
        $admin_white_list->update([
            'ip' => $request->ip,
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

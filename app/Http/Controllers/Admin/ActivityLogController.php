<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\DateFilter;

/**
 * Controller of CRUD activity log
 * @package App\Http\Controllers\Admin
 */
class ActivityLogController extends Controller
{
    protected $model = \App\Models\ActivityLog::class;

    protected $transformer = \App\Transformers\Admin\ActivityLogTransformer::class;

    /**
     * Get activity log list
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $logs = QueryBuilder::for($this->model)
        ->allowedFilters([
            AllowedFilter::exact('causer_id'),
            AllowedFilter::partial('description'),
            AllowedFilter::custom('created_at_between', new DateFilter('activity_logs'))
        ])->orderByDesc('id');

        return $this->paginate($logs, $this->transformer);
    }

    /**
     * Get activity log type list
     * @method GET
     * @return json
     */
    public function type()
    {
        $types = $this->model::orderBy('description')
        ->distinct('description')
        ->pluck('description');

        return [
          "data" => $types
        ];
    }


    /**
     * Delete an activity log via id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $log = $this->model::findOrFail($this->parameters('activity_log'));
        $log->delete();

        return $this->success();
    }
}

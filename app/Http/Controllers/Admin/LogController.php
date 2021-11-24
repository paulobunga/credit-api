<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Log;

class LogController extends Controller
{
    protected $transformer = \App\Transformers\Admin\LogTransformer::class;
    protected $model = null;
    protected $levels = [
       "total",
       "emergency",
       "alert",
       "critical",
       "error",
       "warning",
       "notice",
       "info",
       "debug"
    ];

    public function __construct()
    {
        $this->model = new Log();
    }

    public function index(Request $request)
    {
        $tables = $this->model->getAllTables();
        $results = [];
        foreach ($tables as $table) {
            $this->model->setTable($table);
            $result = $this->model->selectRaw('
              DATE(created_at) AS date, COUNT(id) as total,
              SUM(level_name="emergency") AS emergency, SUM(level_name="alert") as alert, 
              SUM(level_name="critical") AS critical, SUM(level_name="error") AS error,
              SUM(level_name="warning") AS warning, SUM(level_name="notice") AS notice,
              SUM(level_name="info") AS info, SUM(level_name="debug") AS debug
            ')
            ->groupByRaw('DATE(created_at)')
            ->get();
            array_push($results, $result[0]);
        }
        return ['data' => $results];
    }

    public function show(Request $request)
    {
        $this->validate($request, [
            'date' => 'required|in:' .implode(',', $this->model->getAllTables()),
            'level' => 'required|in:' .implode(',', $this->levels),
        ]);

        $this->model->setTable($request->date);
        $logs = $this->model
          -> when($request->level != "total", function($query) use($request) { 
              $query->where('level_name', $request->level);
          })
          -> when($request->get('queryStr', null), function($query) use($request) { 
              $query->where('message', 'like', '%'.$request->queryStr.'%');
          })->paginate($request->perPage);

        return $this->response->withPaginator($logs, $this->transformer);
    }

    public function destroy(Request $request)
    { 
        $this->model->setTable($this->parameters('date'));
        $this->model->deleteTable();
        return $this->success();
    }
}

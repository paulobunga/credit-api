<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Log as LogModel;

class LogController extends Controller
{
    protected $transformer = \App\Transformers\Admin\LogTransformer::class;

    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LogModel();
    }

    public function index(Request $request)
    {
        $tables = LogModel::getAllTables();
        $results = [];
        foreach ($tables as $table) {
            $results[] = $this->model->setTable($table)->selectRaw("
              '{$table}' AS date, 
              COUNT(id) as total,
              COALESCE(SUM(level_name='emergency'),0) AS emergency, 
              COALESCE(SUM(level_name='alert'),0) as alert, 
              COALESCE(SUM(level_name='critical'),0) AS critical, 
              COALESCE(SUM(level_name='error'),0) AS error,
              COALESCE(SUM(level_name='warning'),0) AS warning, 
              COALESCE(SUM(level_name='notice'),0) AS notice,
              COALESCE(SUM(level_name='info'),0) AS info, 
              COALESCE(SUM(level_name='debug'),0) AS debug
            ")->first();
        }

        return ['data' => $results];
    }

    public function show(Request $request)
    {
        $table = $this->parameters('log');
        if (!in_array($table, LogModel::getAllTables())) {
            throw new \Exception("$table is not exists!");
        }
        $this->validate($request, [
            'level' => 'required|in:' . implode(',', LogModel::LEVELS),
        ]);
        $logs = QueryBuilder::for($this->model->setTable($table)->newQuery())
            ->when($request->level != "total", function ($query) use ($request) {
                $query->where('level_name', $request->level);
            })
            ->when($request->get('queryStr', null), function ($query) use ($request) {
                $query->where('message', 'like', '%' . $request->queryStr . '%');
            });

        return $this->paginate($logs, $this->transformer);
    }

    public function destroy(Request $request)
    {
        $table = $this->parameters('log');
        if (!in_array($table, LogModel::getAllTables())) {
            throw new \Exception("$table is not exists!");
        }
        $this->model->setTable($table)->drop();

        return $this->success();
    }
}

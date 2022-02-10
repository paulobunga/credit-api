<?php

namespace App\Http\Controllers\Admin;

use Dingo\Api\Http\Request;
use App\Models\Log as LogModel;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;

class LogController extends Controller
{
    protected $transformer = \App\Transformers\Admin\LogTransformer::class;

    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LogModel();
    }

    /**
     * Get log list
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $tables = LogModel::getAllTables();
        $results = [];
        foreach ($tables as $table) {
            $results[] = $this->model->setTable($table)->selectRaw("
              '{$table}' AS date, 
              COUNT(id) as total,
              COALESCE(SUM(level='emergency'),0) AS emergency, 
              COALESCE(SUM(level='alert'),0) as alert, 
              COALESCE(SUM(level='critical'),0) AS critical, 
              COALESCE(SUM(level='error'),0) AS error,
              COALESCE(SUM(level='warning'),0) AS warning, 
              COALESCE(SUM(level='notice'),0) AS notice,
              COALESCE(SUM(level='info'),0) AS info, 
              COALESCE(SUM(level='debug'),0) AS debug
            ")->first();
        }

        return ['data' => $results];
    }

    /**
     * Return the detail logs by date
     *
     * @param  mixed $request
     * @method GET
     * @return json
     */
    public function show(Request $request)
    {
        $table = $this->parameters('log');
        if (!in_array($table, LogModel::getAllTables())) {
            throw new \Exception("$table is not exists!");
        }
        $this->validate($request, [
            'level' => 'required|in:' . implode(',', LogModel::LEVELS),
        ]);

        $query_str = $request->get('queryStr', null) ? escape_like($request->queryStr) : null;

        $logs = QueryBuilder::for($this->model->setTable($table)->newQuery())
            ->when($request->get('level', null), function ($query) use ($request) {
                $query->where('level', $request->level);
            })
            ->when($query_str, function ($query) use ($query_str) {
                $query->where(function ($query) use ($query_str) {
                    $query->where('message', 'like', '%' . $query_str . '%')
                        ->orWhere('context', 'like', '%' . $query_str . '%');
                });
            })
            ->orderBy('created_at', 'desc');

        if ($query_str) {
            $stats = QueryBuilder::for($this->model->setTable($table)->newQuery())
                ->when($query_str, function ($query) use ($query_str) {
                    $query->where('message', 'like', '%' . $query_str . '%');
                    $query->orWhere('context', 'like', '%' . $query_str . '%');
                })
                ->selectRaw("
                  COUNT(id) as total,
                  COALESCE(SUM(level='emergency'),0) AS emergency, 
                  COALESCE(SUM(level='alert'),0) as alert, 
                  COALESCE(SUM(level='critical'),0) AS critical, 
                  COALESCE(SUM(level='error'),0) AS error,
                  COALESCE(SUM(level='warning'),0) AS warning, 
                  COALESCE(SUM(level='notice'),0) AS notice,
                  COALESCE(SUM(level='info'),0) AS info, 
                  COALESCE(SUM(level='debug'),0) AS debug
                ")
                ->first();

            return $this->paginate($logs, $this->transformer, ["levels" => $stats]);
        }

        return $this->paginate($logs, $this->transformer);
    }

    /**
     * Delete log table by name
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method DELETE
     * @return json
     */
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

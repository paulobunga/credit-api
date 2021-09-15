<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\ValidationHttpException;

abstract class Controller extends BaseController
{
    use Helpers;

    protected $perPage;

    protected $export;

    public function __construct()
    {
        $this->perPage = min(request()->get('per_page', 10), 100);
        $this->export = request()->header('X-Header-Export', false);
    }

    public function validate(
        \Illuminate\Http\Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ) {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $rules,
                $messages,
                $customAttributes
            );
        if ($validator->fails()) {
            throw new ValidationHttpException(
                $validator->errors()
            );
        }
    }

    protected function parameters($name, $default = null)
    {
        $route = app('request')->route();

        return urldecode(Arr::get($route[2], $name, $default));
    }

    protected function success()
    {
        return [
            'message' => 'success'
        ];
    }

    protected function paginate(QueryBuilder $builder, $transformer)
    {
        if ($this->export) {
            $class = ucfirst(Str::singular(array_slice(explode('.', request()->route()[1]['as']), -2, 1)[0]));
            $class = "\\App\\Exports\\{$class}Export";
            return new $class($builder->get());
        }

        if (!empty($this->perPage)) {
            return $this->response->withPaginator($builder->paginate($this->perPage), $transformer);
        } else {
            return $this->response->collection($builder->get(), $transformer);
        }
    }

    public function index(Request $request)
    {
        throw new RouteNotFoundException();
    }

    public function show(Request $request)
    {
        throw new RouteNotFoundException();
    }

    public function store(Request $request)
    {
        throw new RouteNotFoundException();
    }

    public function update(Request $request)
    {
        throw new RouteNotFoundException();
    }

    public function destroy(Request $request)
    {
        throw new RouteNotFoundException();
    }
}

<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request as ValidationRequest;
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

    /**
     * Validate Http request with rules
     * @param \Illuminate\Http\Request $request Http request
     * @param array $rules validation rules
     * @param array $messages invalid messages
     * @param array $customAttributes custom format
     *
     */
    public function validate(
        ValidationRequest $request,
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

    /**
     * Get url parameters
     * @param string $name name of parameter
     * @param string $default default value if parameter not found
     * @return string parameter
     */
    protected function parameters(string $name, string $default = null): string
    {
        $route = app('request')->route();

        return urldecode(Arr::get($route[2], $name, $default));
    }

    /**
     * Default success response
     * @return array
     */
    protected function success(): array
    {
        return [
            'message' => 'success'
        ];
    }

    /**
     *
     */
    protected function paginate(QueryBuilder $builder, $transformer)
    {
        if ($this->export) {
            $routes = array_slice(explode('.', request()->route()[1]['as']), 0, 2);
            $routes = array_map(fn ($r) => ucfirst(Str::camel(Str::singular($r))), $routes);
            $class = '\\App\\Exports\\' . implode('\\', $routes) .'Export';
            return new $class($builder->get());
        }

        if (!empty($this->perPage)) {
            return $this->response->withPaginator($builder->paginate($this->perPage), $transformer);
        } else {
            return $this->response->collection($builder->get(), $transformer);
        }
    }

    /**
     * Default index action, should be overwritten
     * @param \Dingo\Api\Http\Request $request Http request
     */
    public function index(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default show action, should be overwritten
     * @param \Dingo\Api\Http\Request $request Http request
     */
    public function show(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default store action, should be overwritten
     * @param \Dingo\Api\Http\Request $request Http request
     */
    public function store(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default update action, should be overwritten
     * @param \Dingo\Api\Http\Request $request Http request
     */
    public function update(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default destroy action, should be overwritten
     * @param \Dingo\Api\Http\Request $request Http request
     */
    public function destroy(Request $request)
    {
        throw new RouteNotFoundException();
    }
}

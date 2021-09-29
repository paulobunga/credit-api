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

/**
 * Base Http Controller.
 * Implement actions from laravel resource routes and integrate helper functions with Dingo and customize functions.
 * @author Charlie Yuan <youmax210139@gmail.com>
 * @version 1.0
 */
abstract class Controller extends BaseController
{
    use Helpers;

    /**
     * @var int $parPage Define page count of pagination
     */
    protected int $perPage;

    /**
     * @var bool $export Define index action should export or not
     */
    protected bool $export;

    /**
     * Set up perPage and export from request
     * @return void
     */
    public function __construct()
    {
        $this->perPage = min(request()->get('per_page', 10), 100);
        $this->export = request()->header('X-Header-Export', false);
    }

    /**
     * Validate Http request by rules
     * @param \Illuminate\Http\Request $request Http request
     * @param array $rules validation rules
     * @param array $messages invalid messages
     * @param array $customAttributes custom format
     * @throws \App\Exceptions\ValidationHttpException $exception if request not pass rules
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
     * Get URL route parameter
     * @param string $name Name of url route parameter
     * @param string $default Default value if url route parameter not found
     * @return string
     */
    protected function parameters(string $name, string $default = null): string
    {
        $route = app('request')->route();

        return urldecode(Arr::get($route[2], $name, $default));
    }

    /**
     * Default success response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'success'
        ]);
    }

    /**
     * Export csv blob response or json response by query header
     * @param \Spatie\QueryBuilder\QueryBuilder $builder Query builder
     * @param string|callable|object $transformer transform model to reseponse
     * @return \Dingo\Api\Http\Response
     */
    protected function paginate(QueryBuilder $builder, $transformer)
    {
        if ($this->export) {
            $routes = array_slice(explode('.', request()->route()[1]['as']), 0, 2);
            $routes = array_map(fn ($r) => ucfirst(Str::camel(Str::singular($r))), $routes);
            $class = '\\App\\Exports\\' . implode('\\', $routes) . 'Export';
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
     * @param \Dingo\Api\Http\Request $request
     * @throws \App\Exceptions\RouteNotFoundException $exception if method is not overrided
     */
    public function index(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default show action, should be overwritten
     * @param \Dingo\Api\Http\Request $request
     * @throws \App\Exceptions\RouteNotFoundException $exception if method is not overrided
     */
    public function show(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default store action, should be overwritten
     * @param \Dingo\Api\Http\Request $request
     * @throws \App\Exceptions\RouteNotFoundException $exception if method is not overrided
     */
    public function store(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default update action, should be overwritten
     * @param \Dingo\Api\Http\Request $request
     * @throws \App\Exceptions\RouteNotFoundException $exception if method is not overrided
     */
    public function update(Request $request)
    {
        throw new RouteNotFoundException();
    }

    /**
     * Default destroy action, should be overwritten
     * @param \Dingo\Api\Http\Request $request
     * @throws \App\Exceptions\RouteNotFoundException $exception
     */
    public function destroy(Request $request)
    {
        throw new RouteNotFoundException();
    }
}

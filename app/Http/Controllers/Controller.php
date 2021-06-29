<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Exceptions\RouteNotFoundException;
use Dingo\Api\Http\Request;
use Illuminate\Support\Arr;

abstract class Controller extends BaseController
{
    use Helpers;

    public function __construct()
    {
        $this->perPage = min(request()->get('per_page', 10), 100);
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

    public function index()
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

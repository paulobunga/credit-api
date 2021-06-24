<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    function (\Dingo\Api\Routing\Router $api) {
        $api->group([
            'namespace' => 'App\Http\Controllers\Api',
            ['middleware' => 'api.throttle', 'limit' => 100, 'expires' => 5],
        ], function ($api) {
            $api->post('/deposits', [
                'uses' => 'DepositController@create',
                'as' => "deposits.create"
            ]);
        });
    }
);

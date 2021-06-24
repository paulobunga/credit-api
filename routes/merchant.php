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


$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    ['middleware' => 'api.throttle', 'limit' => 1000, 'expires' => 5],
    function (\Dingo\Api\Routing\Router $api) {
        $api->group([
            'namespace' => 'App\Http\Controllers\Merchant',
            'middleware' => []
        ], function ($api) {
            $api->post('merchants/auth/login', [
                'uses' => 'AuthController@login',
                'as' => "merchants.login"
            ]);
            $api->group([
                'middleware' => ['api.auth']
            ], function ($api) {
                $api->post('merchants/auth/logout', 'AuthController@logout');
                $api->post('merchants/auth/refresh', 'AuthController@refresh');
                $api->post('merchants/auth/me', 'AuthController@me');
            });
        });
    }
);

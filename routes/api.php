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
    ['middleware' => 'api.throttle', 'limit' => 100, 'expires' => 5],
    function (\Dingo\Api\Routing\Router $api) {

        $api->group([
            'middleware' => [],
            'namespace' => 'App\Http\Controllers\Api',
        ], function ($api) {
            $api->post('/auth/login', 'AuthController@login');
            $api->get('/admins', 'AdminController@index');
            $api->post('/auth/logout', 'AuthController@logout');
            $api->post('/auth/refresh', 'AuthController@refresh');
            $api->post('/auth/me', 'AuthController@me');

            $api->get('/permissions', 'PermissionController@index');
        });
    }
);

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

function resource($api, $uri, $controller, $optional = '{id}')
{
    $api->get("/{$uri}", [
        'uses' => "{$controller}@index",
        'as' => "{$uri}.index"
    ]);
    $api->post("/$uri/create", [
        'uses' => "{$controller}@create",
        'as' => "{$uri}.create"
    ]);
    $api->put("/{$uri}/{$optional}/edit", [
        'uses' => "{$controller}@edit",
        'as' => "{$uri}.edit"
    ]);
    $api->delete("/{$uri}/{$optional}/destroy", [
        'uses' => "{$controller}@destroy",
        'as' => "{$uri}.destroy"
    ]);
};

$api->version(
    'v1',
    ['middleware' => 'api.throttle', 'limit' => 100, 'expires' => 5],
    function (\Dingo\Api\Routing\Router $api) {

        $api->group([
            'middleware' => [],
            'namespace' => 'App\Http\Controllers\Api',
        ], function ($api) {
            $api->post('/auth/login', 'AuthController@login');
            $api->post('/auth/logout', 'AuthController@logout');
            $api->post('/auth/refresh', 'AuthController@refresh');
            $api->post('/auth/me', 'AuthController@me');

            resource($api, 'permissions', 'PermissionController', '{name}');
            resource($api, 'roles', 'RoleController', '{name}');
            resource($api, 'admins', 'AdminController', '{name}');
            resource($api, 'admin_white_lists', 'AdminWhiteListController');
            resource($api, 'merchants', 'MerchantController', '{name}');
        });
    }
);

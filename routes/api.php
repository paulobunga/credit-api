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
    $alias = str_replace('/', '.', $uri);
    $api->get("/{$uri}", [
        'uses' => "{$controller}@index",
        'as' => "{$alias}.index"
    ]);
    $api->post("/$uri/create", [
        'uses' => "{$controller}@create",
        'as' => "{$alias}.create"
    ]);
    $api->put("/{$uri}/{$optional}/edit", [
        'uses' => "{$controller}@edit",
        'as' => "{$alias}.edit"
    ]);
    $api->delete("/{$uri}/{$optional}/destroy", [
        'uses' => "{$controller}@destroy",
        'as' => "{$alias}.destroy"
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
            resource($api, 'merchant/deposits', 'MerchantDepositController', '{name}');
            resource($api, 'resellers', 'ResellerController', '{name}');
            resource($api, 'reseller/deposits', 'ResellerDepositController', '{name}');
        });
    }
);

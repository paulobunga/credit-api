<?php

$api->version(
    'v1',
    ['middleware' => 'api.throttle', 'limit' => 100, 'expires' => 5],
    function (\Dingo\Api\Routing\Router $api) {
        $api->group([
            'namespace' => 'App\Http\Controllers\Merchant',
            'prefix' => 'merchant',
            'middleware' => [
                "domain:" . env('PRIVATE_DOMAIN'),
            ],
        ], function ($api) {
            $api->post('/auth/login', 'AuthController@login');
            $api->group([
                'middleware' => [
                    'api.auth:merchant'
                ]
            ], function ($api) {
                $api->post('/auth/logout', 'AuthController@logout');
                $api->post('/auth/refresh', 'AuthController@refresh');
                $api->post('/auth/me', 'AuthController@me');
            });
        });
    }
);

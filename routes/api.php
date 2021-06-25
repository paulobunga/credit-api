<?php

$api->version(
    'v1',
    ['middleware' => 'api.throttle', 'limit' => 100, 'expires' => 5],
    function (\Dingo\Api\Routing\Router $api) {
        $api->group([
            'namespace' => 'App\Http\Controllers\Api',
            'middleware' => [
                'domain:' . env('PUBLIC_DOMAIN'),
            ],
        ], function ($api) {
            $api->resource('deposits', 'DepositController', [
                'only' => ['index', 'store']
            ]);
        });
    }
);

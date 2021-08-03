<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Reseller',
    'prefix' => 'reseller',
    'as' => 'reseller',
    'middleware' => [
        "domain:" . env('PRIVATE_DOMAIN'),
    ],
], function ($api) {
    $api->post('/auth/login', ['as' => 'auth.login', 'uses' => 'AuthController@login']);
    $api->post('/auth/register', ['as' => 'auth.register', 'uses' => 'AuthController@register']);
    $api->group([
        'middleware' => [
            'api.auth:reseller'
        ]
    ], function ($api) {
        # auth
        $api->post('/auth/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);
        $api->post('/auth/refresh', ['as' => 'auth.refresh', 'uses' => 'AuthController@refresh']);
        $api->post('/auth/me', ['as' => 'auth.me', 'uses' => 'AuthController@me']);
        $api->put("/auth/update", ['as' => 'auth.update', 'uses' => 'AuthController@update']);

        $api->resource('banks', 'BankController', ['only' => ['index']]);
        $api->resource('bankcards', 'BankCardController');
        $api->resource('deposits', 'DepositController', ['only' => ['index', 'update']]);
        $api->resource('withdrawals', 'WithdrawalController', ['only' => ['index', 'store']]);
        $api->resource('reports', 'ReportController', ['only' => ['index']]);
        $api->get("/reports/month", ['as' => 'reports.month', 'uses' => 'ReportController@month']);
    });
});

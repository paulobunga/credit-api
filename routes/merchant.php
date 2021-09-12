<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Merchant',
    'prefix' => 'merchant',
    'as' => 'merchant',
    'middleware' => [
        "domain:" . env('PRIVATE_DOMAIN'),
    ],
], function ($api) {
    $api->post('/auth/login', ['as' => 'auth.login', 'uses' => 'AuthController@login']);
    $api->group([
        'middleware' => [
            'api.auth:merchant',
            'whitelist:merchant_backend'
        ]
    ], function ($api) {
        # auth
        $api->post('/auth/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);
        $api->post('/auth/refresh', ['as' => 'auth.refresh', 'uses' => 'AuthController@refresh']);
        $api->post('/auth/me', ['as' => 'auth.me', 'uses' => 'AuthController@me']);
        $api->put("/auth/update", ['as' => 'auth.update', 'uses' => 'AuthController@update']);
        $api->put("/auth/renew", ['as' => 'auth.renew', 'uses' => 'AuthController@renew']);
        $api->put("/auth/whitelist", ['as' => 'auth.whitelist', 'uses' => 'AuthController@whitelist']);

        $api->resource('deposits', 'DepositController', ['only' => ['index']]);
        $api->put("/deposits/resend/{deposit}", ['as' => 'deposits.resend', 'uses' => 'DepositController@resend']);

        $api->resource('withdrawals', 'WithdrawalController', ['only' => ['index', 'store']]);

        $api->resource('reports', 'ReportController', ['only' => ['index']]);
        $api->get("/reports/month", ['as' => 'reports.month', 'uses' => 'ReportController@month']);
    });
});

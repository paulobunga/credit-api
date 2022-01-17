<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Api',
    'as' => 'api',
    'middleware' => [
        'domain:' . env('PUBLIC_DOMAIN'),
    ],
], function ($api) {
    if (env('APP_ENV') !== 'production') {
        $api->get("/demos/payin", ['as' => 'demos.payin.create', 'uses' => 'DemoController@payin']);
        $api->post("/demos/payin", ['as' => 'demos.payin.store', 'uses' => 'DemoController@payin']);
        $api->get("/demos/payout", ['as' => 'demos.payout.create', 'uses' => 'DemoController@payout']);
        $api->post("/demos/payout", ['as' => 'demos.payout.store', 'uses' => 'DemoController@payout']);
        $api->post("/demos/callback", ['as' => 'demos.callback', 'uses' => 'DemoController@callback']);
    }

    $api->get("/pay/deposits", ['as' => 'deposits.pay', 'uses' => 'DepositController@pay']);
    $api->get("/pay/withdrawals", ['as' => 'withdrawals.pay', 'uses' => 'WithdrawalController@pay']);

    $api->group([
        'middleware' => [
            'whitelist:merchant_api'
        ],
    ], function ($api) {

        $api->resource('deposits', 'DepositController', ['except' => ['destroy']]);

        $api->resource('withdrawals', 'WithdrawalController', ['except' => ['destroy']]);
    });
});

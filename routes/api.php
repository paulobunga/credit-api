<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Api',
    'as' => 'api',
    'middleware' => [
        'domain:' . env('PUBLIC_DOMAIN'),
    ],
], function ($api) {
    $api->get("/demos/payin", ['as' => 'demos.payin.create', 'uses' => 'DemoController@payin']);
    $api->post("/demos/payin", ['as' => 'demos.payin.store', 'uses' => 'DemoController@payin']);
    
    $api->get("/pay/deposits", ['as' => 'deposits.pay', 'uses' => 'DepositController@pay']);
    $api->group([
        'middleware' => [
            'whitelist:merchant_api'
        ],
    ], function ($api) {
        $api->resource('deposits', 'DepositController', [
            'only' => ['index', 'store', 'show', 'update']
        ]);
    });
});

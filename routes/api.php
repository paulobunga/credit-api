<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Api',
    'as' => 'api',
    'middleware' => [
        'domain:' . env('PUBLIC_DOMAIN'),
        'whitelist:merchant_api'
    ],
], function ($api) {
    $api->resource('deposits', 'DepositController', [
        'only' => ['index', 'store', 'show', 'update']
    ]);
    $api->get("/pay/deposits", ['as' => 'deposits.pay', 'uses' => 'DepositController@pay']);
});

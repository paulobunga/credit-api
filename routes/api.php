<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Api',
    'as' => 'api',
    'middleware' => [
        'domain:' . env('PUBLIC_DOMAIN'),
    ],
], function ($api) {
    $api->resource('deposits', 'DepositController', [
        'only' => ['index', 'store']
    ]);
    $api->get("/deposits/pay", ['as' => 'deposits.pay', 'uses' => 'DepositController@pay']);
});

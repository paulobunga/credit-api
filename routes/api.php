<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Api',
    'as' => 'api',
    'middleware' => [
        'domain:' . env('PUBLIC_DOMAIN'),
    ],
], function ($api) {
    $api->resource('deposits', 'DepositController', [
        'only' => ['index', 'store', 'show', 'update']
    ]);
    $api->get("/pay/deposits", ['as' => 'deposits.pay', 'uses' => 'DepositController@pay']);
});

<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Reseller',
    'prefix' => 'reseller',
    'as' => 'reseller',
    'middleware' => [
        "domain:" . env('PRIVATE_DOMAIN'),
    ],
], function ($api) {
    $api->get('/auth/setting', ['as' => 'auth.setting', 'uses' => 'AuthController@setting']);
    $api->post('/auth/login', ['as' => 'auth.login', 'uses' => 'AuthController@login']);
    $api->post('/auth/register', ['as' => 'auth.register', 'uses' => 'AuthController@register']);
    $api->group([
        'middleware' => [
            'api.auth:reseller'
        ]
    ], function ($api) {
        # auth
        // $api->resource('devices', 'DeviceController', ['only' => ['store','destroy']]);
        $api->post('/auth/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);
        $api->post('/auth/refresh', ['as' => 'auth.refresh', 'uses' => 'AuthController@refresh']);
        $api->post('/auth/me', ['as' => 'auth.me', 'uses' => 'AuthController@me']);
        $api->put("/auth/update", ['as' => 'auth.update', 'uses' => 'AuthController@update']);
        $api->put("/auth/activate", ['as' => 'auth.activate', 'uses' => 'AuthController@activate']);
        $api->post('/auth/channel', ['as' => 'auth.channel', 'uses' => 'AuthController@channel']);
        $api->post('/auth/onesignal', ['as' => 'auth.onesignal', 'uses' => 'AuthController@onesignal']);
        $api->put('/auth/pay', ['as' => 'auth.pay', 'uses' => 'AuthController@pay']);

        $api->resource('activate_codes', 'ActivateCodeController', ['only' => ['index', 'store']]);

        $api->resource('banks', 'BankController', ['only' => ['index']]);
        
        $api->resource('bankcards', 'BankCardController');
        $api->put("/bankcards/status/{bankcard}", [
            'as' => 'bankcards.status', 'uses' => 'BankCardController@status'
        ]);
        
        $api->resource('deposits', 'DepositController', ['only' => ['index', 'update']]);

        $api->resource('payment_channels', 'PaymentChannelController', ['only' => ['index']]);
        
        $api->resource('withdrawals', 'WithdrawalController', ['only' => ['index', 'update']]);
        $api->get("/withdrawals/slip/{withdrawal}", [
            'as' => 'withdrawals.slip', 'uses' => 'WithdrawalController@slip'
        ]);

        $api->resource('settlements', 'SettlementController', ['only' => ['index', 'store']]);

        $api->resource('sms', 'SmsController', ['only' => ['store']]);
        
        $api->resource('reports', 'ReportController', ['only' => ['index']]);
    });
});

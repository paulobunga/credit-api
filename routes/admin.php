<?php
$api->group([
    'namespace' => 'App\Http\Controllers\Admin',
    'prefix' => 'admin',
    'as' => 'admin',
    'middleware' => [
        "domain:" . env('PRIVATE_DOMAIN'),
    ],
], function ($api) {
    $api->post('/auth/login', ['as' => 'auth.login', 'uses' => 'AuthController@login']);
    $api->group([
        'middleware' => [
            'api.auth:admin',
            'whitelist:admin',
            'role_or_permission:Super Admin',
        ],
    ], function ($api) {
        $api->post('/auth/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);
        $api->post('/auth/refresh', ['as' => 'auth.refresh', 'uses' => 'AuthController@refresh']);
        $api->post('/auth/me', ['as' => 'auth.me', 'uses' => 'AuthController@me']);

        $api->resource('permissions', 'PermissionController', ['except' => ['show']]);
        $api->resource('roles', 'RoleController', ['except' => ['show']]);
        $api->resource('banks', 'BankController', ['except' => ['show']]);
        $api->get('/export/banks', ['as' => 'banks.export', 'uses' => 'BankController@export']);
        $api->resource('admins', 'AdminController', ['except' => ['show']]);
        $api->resource('settings', 'SettingController', ['except' => ['show']]);
        $api->resource('merchant_white_lists', 'MerchantWhiteListController', ['except' => ['show']]);

        $api->resource('merchants', 'MerchantController', ['except' => ['show']]);
        $api->put("/merchants/renew/{merchant}", ['as' => 'merchants.renew', 'uses' => 'MerchantController@renew']);
        $api->put("/merchants/whitelist/{merchant}", [
            'as' => 'merchants.whitelist', 'uses' => 'MerchantController@whitelist'
        ]);

        $api->resource('merchant_deposits', 'MerchantDepositController', ['only' => ['index', 'update']]);
        $api->resource('merchant_withdrawals', 'MerchantWithdrawalController', ['only' => ['index']]);
        $api->resource('payment_channels', 'PaymentChannelController', ['except' => ['show']]);

        $api->resource('resellers', 'ResellerController', ['except' => ['show']]);
        $api->put("/resellers/deposit/{reseller}", [
            'as' => 'resellers.deposit', 'uses' => 'ResellerController@deposit'
        ]);
        $api->resource('reseller_bank_cards', 'ResellerBankCardController', ['only' => ['index', 'update', 'destroy']]);
        // $api->resource('reseller_deposits', 'ResellerDepositController');
        $api->resource('reseller_withdrawals', 'ResellerWithdrawalController', ['only' => ['index', 'update']]);

        $api->get("/report/resellers", [
            'uses' => "ReportController@reseller",
            'as' => "report.resellers.index"
        ]);
        $api->get("/report/merchants", [
            'uses' => "ReportController@merchant",
            'as' => "report.merchants.index"
        ]);
    });
});

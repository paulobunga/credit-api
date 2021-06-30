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
            'api.auth:admin'
        ],
    ], function ($api) {
        $api->post('/auth/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);
        $api->post('/auth/refresh', ['as' => 'auth.refresh', 'uses' => 'AuthController@refresh']);
        $api->post('/auth/me', ['as' => 'auth.me', 'uses' => 'AuthController@me']);

        $api->resource('permissions', 'PermissionController');
        $api->resource('roles', 'RoleController');
        $api->resource('banks', 'BankController');
        $api->resource('admins', 'AdminController');
        $api->resource('admin_white_lists', 'AdminWhiteListController');
        $api->resource('merchant_white_lists', 'MerchantWhiteListController');

        $api->resource('merchants', 'MerchantController');
        $api->put("/merchants/renew/{merchant}", ['as' => 'merchants.renew', 'uses' => 'MerchantController@renew']);

        $api->resource('merchant_deposits', 'MerchantDepositController');
        $api->resource('merchant_withdrawals', 'MerchantWithdrawalController');

        $api->resource('resellers', 'ResellerController');
        $api->resource('reseller_bank_cards', 'ResellerBankCardController');
        $api->resource('reseller_deposits', 'ResellerDepositController');
        $api->resource('reseller_withdrawals', 'ResellerWithdrawalController');

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

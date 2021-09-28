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
        ],
    ], function ($api) {
        $api->post('/auth/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);
        $api->post('/auth/refresh', ['as' => 'auth.refresh', 'uses' => 'AuthController@refresh']);
        $api->post('/auth/me', ['as' => 'auth.me', 'uses' => 'AuthController@me']);

        $api->group([
            'middleware' => [
                'role_or_permission:Super Admin,admin',
            ],
        ], function ($api) {
            $api->resource('permissions', 'PermissionController', ['except' => ['show']]);

            $api->resource('roles', 'RoleController', ['except' => ['show']]);

            $api->resource('banks', 'BankController', ['except' => ['show']]);

            $api->resource('admins', 'AdminController', ['except' => ['show']]);

            $api->resource('settings', 'SettingController', ['only' => ['index', 'update']]);

            $api->resource('merchants', 'MerchantController', ['except' => ['show']]);
            $api->put("/merchants/renew/{merchant}", [
                'as' => 'merchants.renew', 'uses' => 'MerchantController@renew'
            ]);
            $api->put("/merchants/whitelist/{merchant}", [
                'as' => 'merchants.whitelist', 'uses' => 'MerchantController@whitelist'
            ]);
            $api->put("/merchants/fee/{merchant}", [
                'as' => 'merchants.fee', 'uses' => 'MerchantController@fee'
            ]);
            $api->put("/merchants/reset_password/{merchant}", [
                'as' => 'merchants.reset_password', 'uses' => 'MerchantController@reset'
            ]);

            $api->resource('merchant_deposits', 'MerchantDepositController', ['only' => ['index', 'update']]);
            $api->put("/merchant_deposits/resend/{merchant_deposit}", [
                'as' => 'merchant_deposits.resend', 'uses' => 'MerchantDepositController@resend'
            ]);

            $api->resource('merchant_withdrawals', 'MerchantWithdrawalController', ['only' => ['index']]);

            $api->resource('payment_channels', 'PaymentChannelController', ['except' => ['show']]);

            $api->resource('resellers', 'ResellerController', ['except' => ['show']]);
            $api->put("/resellers/deposit/{reseller}", [
                'as' => 'resellers.deposit', 'uses' => 'ResellerController@deposit'
            ]);
            $api->put("/resellers/withdrawal/{reseller}", [
                'as' => 'resellers.withdrawal', 'uses' => 'ResellerController@withdrawal'
            ]);
            $api->put("/resellers/reset_password/{reseller}", [
                'as' => 'resellers.reset_password', 'uses' => 'ResellerController@resetPassword'
            ]);
            $api->put("/resellers/make_up/{reseller}", [
                'as' => 'resellers.makeUp', 'uses' => 'ResellerController@makeUp'
            ]);

            $api->resource('reseller_bank_cards', 'ResellerBankCardController', [
                'only' => ['index', 'update', 'destroy']
            ]);

            $api->resource('reseller_deposits', 'ResellerDepositController', ['only' => ['index', 'update']]);

            $api->resource('reseller_withdrawals', 'ResellerWithdrawalController', ['only' => ['index', 'update']]);

            $api->get("/report/resellers", [
                'uses' => "ReportController@reseller",
                'as' => "report_resellers.index"
            ]);
            $api->get("/report/merchants", [
                'uses' => "ReportController@merchant",
                'as' => "report_merchants.index"
            ]);
        });
    });
});

<?php

$api->version(
    'v1',
    ['middleware' => 'api.throttle', 'limit' => 100, 'expires' => 5],
    function (\Dingo\Api\Routing\Router $api) {
        $api->group([
            'middleware' => [
                "domain:" . env('PRIVATE_DOMAIN'),
            ],
            'prefix' => 'admin',
            'namespace' => 'App\Http\Controllers\Admin',
        ], function ($api) {
            $api->post('/auth/login', 'AuthController@login');
            $api->group([
                'middleware' => [
                    'api.auth:admin'
                ],
            ], function ($api) {
                $api->post('/auth/logout', 'AuthController@logout');
                $api->post('/auth/refresh', 'AuthController@refresh');
                $api->post('/auth/me', 'AuthController@me');

                $api->resource('permissions', 'PermissionController');
                $api->resource('roles', 'RoleController');
                $api->resource('banks', 'BankController');
                $api->resource('admins', 'AdminController');
                $api->resource('admin_white_lists', 'AdminWhiteListController');

                $api->resource('merchants', 'MerchantController');
                $api->put("/merchants/renew/{merchant}", [
                    'uses' => "MerchantController@renew",
                    'as' => "merchants.renew"
                ]);
                $api->resource('merchant_deposits', 'MerchantDepositController');
                $api->resource('merchant_withdrawals', 'MerchantWithdrawalController');
                $api->resource('merchant_fund_records', 'MerchantFundRecordController');

                $api->resource('resellers', 'ResellerController');
                $api->resource('reseller_bank_cards', 'ResellerBankCardController');
                $api->resource('reseller_deposits', 'ResellerDepositController');
                $api->resource('reseller_withdrawals', 'ResellerWithdrawalController');
                $api->resource('reseller_fund_records', 'ResellerFundRecordController');

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
    }
);

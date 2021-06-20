<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminSeeder::class,
            AdminWhiteListSeeder::class,
            BankSeeder::class,
            ResellerSeeder::class,
            ResellerDepositSeeder::class,
            ResellerWithdrawalSeeder::class,
            ResellerBankCardSeeder::class,
            MerchantSeeder::class,
            MerchantDepositSeeder::class,
            MerchantWithdrawalSeeder::class
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\TransactionMethod;
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
            BankSeeder::class,
            ResellerSeeder::class,
            ResellerDepositSeeder::class,
            ResellerWithdrawalSeeder::class,
            ResellerBankCardSeeder::class,
            MerchantSeeder::class,
            MerchantDepositSeeder::class,
            MerchantWithdrawalSeeder::class,
            WhiteListSeeder::class,
            ReportSeeder::class,
        ]);
    }
}

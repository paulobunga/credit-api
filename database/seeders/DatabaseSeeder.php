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
            PermissionAndRoleSeeder::class,
            AdminSeeder::class,
            BankSeeder::class,
            ResellerSeeder::class,
            ResellerBankCardSeeder::class,
            MerchantSeeder::class,
            MerchantDepositSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Reseller;
use App\Models\PaymentMethod;
use App\Models\ResellerDeposit;

class ResellerDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Reseller::all() as $reseller) {
            $admin = Admin::inRandomOrder()->first();
            $transaction_method_id = [
                1
            ];
            ResellerDeposit::factory()->create([
                'reseller_id' => $reseller->id,
                'admin_id' => $admin->id,
            ])->transactions()->create([
                'transaction_method_id' => $transaction_method_id[array_rand($transaction_method_id)],
                'amount' => rand(20, 10000)
            ]);
        }
    }
}

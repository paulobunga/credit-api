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
            $payment_method = PaymentMethod::inRandomOrder()->first();
            ResellerDeposit::factory()->create([
                'reseller_id' => $reseller->id,
                'admin_id' => $admin->id,
                'payment_method_id' => $payment_method->id
            ]);
        }
    }
}

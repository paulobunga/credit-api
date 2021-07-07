<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = \App\Models\PaymentMethod::count();
        foreach (\App\Models\Bank::factory()->count(16)->make() as $key => $bank) {
            $bank->payment_method_id = $key % $count + 1;
            $bank->save();
        }
    }
}

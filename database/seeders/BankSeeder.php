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
        foreach (\App\Models\Bank::factory()->count(10)->make() as $bank) {
            $bank->payment_method_id = \App\Models\PaymentMethod::inRandomOrder()->first()->id;
            $bank->save();
        }
    }
}

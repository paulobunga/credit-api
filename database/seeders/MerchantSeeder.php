<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Merchant::create([
            'uuid' => '224d4a1f-6fc5-4039-bd81-fcbc7f88c659',
            'name' => 'Test Merchant',
            'username' => 'merchant@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'transaction_fee' => 0.01,
            'credit' => 2000,
            'callback_url' => 'http://google.com.tw',
            'status' => true,
        ]);
        \App\Models\Merchant::factory()->count(3)->create();
    }
}

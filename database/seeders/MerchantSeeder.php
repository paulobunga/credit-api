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
            'name' => 'Test Merchant',
            'username' => 'merchant@gmail.com',
            'password' => 'P@ssw0rd',
            'callback_url' => 'http://google.com.tw',
            'status' => true,
        ]);
        \App\Models\Merchant::factory()->count(3)->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ResellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Reseller::create([
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'transaction_fee' => 0.01,
            'credit' => 2000000,
            'coin' => 0,
            'pending_limit' => 5,
            'status' => true,
        ]);
        \App\Models\Reseller::factory()->count(4)->create();
    }
}

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
            'level' => \App\Models\Reseller::getLevelID('reseller'),
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'commission_percentage' => 0.003,
            'credit' => 2000000,
            'coin' => 0,
            'pending_limit' => 5,
            'status' => true,
        ]);
        \App\Models\Reseller::factory()->count(4)->create();
    }
}

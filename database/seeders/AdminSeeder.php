<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::factory()->create([
            'name' => 'AdminMarket0',
            'username' => 'admin0@gmail.com'
        ]);
        $admin->assignRole('Market');
        $admin = Admin::factory()->create([
            'name' => 'AdminIT0',
            'username' => 'admin1@gmail.com'
        ]);
        $admin->assignRole('IT');
    }
}

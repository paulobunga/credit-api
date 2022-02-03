<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Settings\AdminSetting;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(AdminSetting $as)
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
        $as->white_lists = [internal_gateway_ip()];
        $as->save();
    }
}

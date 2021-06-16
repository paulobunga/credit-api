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
        $admin = Admin::factory()->count(1)->create()->first();
        $admin->assignRole('Market');
        $admin = Admin::factory()->count(1)->create()->first();
        $admin->assignRole('IT');
    }
}

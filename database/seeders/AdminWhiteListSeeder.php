<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\AdminWhiteList;

class AdminWhiteListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Admin::all() as $admin) {
            AdminWhiteList::factory()->create([
                'admin_id' => $admin->id
            ]);
        }
    }
}

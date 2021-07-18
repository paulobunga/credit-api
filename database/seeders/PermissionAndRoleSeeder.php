<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Models\Permission;
use App\Models\Role;

class PermissionAndRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create default permission based on route
        Artisan::call('permission:list');
        
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Market']);
        Role::create(['name' => 'IT']);
    }
}

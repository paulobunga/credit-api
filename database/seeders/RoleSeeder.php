<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create(['name' => 'Super Admin']);
        $role->givePermissionTo('permissions', 'roles', 'admins');
        $role = Role::create(['name' => 'Market']);
        $role->givePermissionTo('admins');
        $role = Role::create(['name' => 'IT']);
        $role->givePermissionTo('admins');
    }
}

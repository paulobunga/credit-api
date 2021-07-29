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
        $role = Role::create(['name' => 'Market']);
        $role->givePermissionTo(
            Permission::whereNotIn('name', ['admin.permissions.index'])->pluck('name')->toArray()
        );
        $role = Role::create(['name' => 'IT']);
        $role->givePermissionTo(
            Permission::whereNotIn('name', [
                'admin.admins.index',
                'admin.admins.store',
                'admin.admins.update',
                'admin.admins.destory',
            ])->pluck('name')->toArray()
        );
    }
}

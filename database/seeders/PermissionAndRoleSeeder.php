<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Models\Role;
use App\Models\Team;
use App\Models\Permission;

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
            Permission::whereNotIn('name', [
                'admin.permissions.index',
                'admin.roles.index',
                'admin.reseller_deposits.update',
                'admin.reseller_withdrawals.update',
            ])->pluck('name')->toArray()
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
        # create default team based on currency
        foreach (app('settings.currency')->currency as $currency => $_) {
            foreach (Team::TYPE as $type) {
                Team::create([
                    'name' => 'Default',
                    'type' => $type,
                    'currency' => $currency,
                    'description' => "Default {$currency} {$type} Team",
                ]);
            }
        }
    }
}

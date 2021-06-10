<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (DB::getDoctrineSchemaManager()->listTableNames() as $table) {
            if ($table == 'migrations') {
                continue;
            }
            if (!Permission::where('name', 'like', $table)->count()) {
                Permission::create(['name' => "create_$table"]);
                Permission::create(['name' => "edit_$table"]);
                Permission::create(['name' => "view_$table"]);
                Permission::create(['name' => "delete_$table"]);
            }
        }
    }
}

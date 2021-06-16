<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

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
                Permission::firstOrCreate(['name' => "$table"]);
                Permission::firstOrCreate(['name' => "$table.index"]);
                Permission::firstOrCreate(['name' => "$table.create"]);
                Permission::firstOrCreate(['name' => "$table.edit"]);
                Permission::firstOrCreate(['name' => "$table.destroy"]);
            }
        }
    }
}

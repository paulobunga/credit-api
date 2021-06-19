<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ResellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Reseller::factory()->count(1)->create();
    }
}

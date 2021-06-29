<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionMethod;

class TransactionMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TransactionMethod::factory()->count(2)->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\ResellerWithdrawal;

class ResellerWithdrawalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Reseller::all() as $reseller) {
            ResellerWithdrawal::factory()->create([
                'reseller_id' => $reseller->id
            ]);
        }
    }
}

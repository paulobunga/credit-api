<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\ResellerWithdrawal;

class ResellerWithdrawalSeeder extends Seeder
{
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Reseller::all() as $reseller) {
            ResellerWithdrawal::create([
                'reseller_id' => $reseller->id,
                'amount' => $this->faker->randomNumber(5),
                'status' => $this->faker->numberBetween(
                    ResellerWithdrawal::STATUS['PENDING'],
                    ResellerWithdrawal::STATUS['REJECTED']
                ),
            ]);
        }
    }
}

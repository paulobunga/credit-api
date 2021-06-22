<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\ResellerWithdrawal;
use App\Models\ResellerDeposit;
use App\Models\ResellerFundRecord;

class ResellerFundRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (ResellerDeposit::all() as $deposit) {
            foreach (range(1, rand(1, 3)) as $i) {
                $deposit->fundRecords()->create(
                    ResellerFundRecord::factory()->make()->toArray()
                );
            }
        }
        foreach (ResellerWithdrawal::all() as $withdrawal) {
            foreach (range(1, rand(1, 3)) as $i) {
                $withdrawal->fundRecords()->create(
                    ResellerFundRecord::factory()->make()->toArray()
                );
            }
        }
    }
}

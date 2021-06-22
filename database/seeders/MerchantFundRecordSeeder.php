<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MerchantWithdrawal;
use App\Models\MerchantDeposit;
use App\Models\MerchantFundRecord;

class MerchantFundRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (MerchantDeposit::all() as $deposit) {
            foreach (range(1, rand(1, 3)) as $i) {
                $deposit->fundRecords()->create(
                    MerchantFundRecord::factory()->make()->toArray()
                );
            }
        }
        foreach (MerchantWithdrawal::all() as $withdrawal) {
            foreach (range(1, rand(1, 3)) as $i) {
                $withdrawal->fundRecords()->create(
                    MerchantFundRecord::factory()->make()->toArray()
                );
            }
        }
    }
}

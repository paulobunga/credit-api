<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
        $type = [0, 2];
        foreach (ResellerDeposit::all() as $deposit) {
            foreach (range(1, rand(1, 3)) as $i) {
                $deposit->fundRecords()->create(
                    array_merge(
                        ResellerFundRecord::factory()->make()->toArray(),
                        ['type' => $type[array_rand($type)]]
                    )
                );
            }
        }
        $type = [1, 3];
        foreach (ResellerWithdrawal::all() as $withdrawal) {
            foreach (range(1, rand(1, 3)) as $i) {
                $withdrawal->fundRecords()->create(
                    array_merge(
                        ResellerFundRecord::factory()->make()->toArray(),
                        ['type' => $type[array_rand($type)]]
                    )
                );
            }
        }
    }
}

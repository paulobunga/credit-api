<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportMonthlyMerchant;
use App\Models\ReportMonthlyReseller;
use App\Models\Merchant;
use App\Models\Reseller;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Merchant::all() as $merchant) {
            foreach (range(6, 1) as $i) {
                foreach ($merchant->credits as $credit) {
                    ReportMonthlyMerchant::factory()->create([
                        'merchant_id' => $merchant->id,
                        'date' => Carbon::now()->subMonths($i)->startOfMonth()->format('Y-m-d'),
                        'currency' => $credit->currency
                    ]);
                }
            }
        }
        foreach (Reseller::all() as $reseller) {
            foreach (range(6, 1) as $i) {
                ReportMonthlyReseller::factory()->create([
                    'reseller_id' => $reseller->id,
                    'date' => Carbon::now()->subMonths($i)->startOfMonth()->format('Y-m-d')
                ]);
            }
        }
    }
}

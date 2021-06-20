<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\MerchantWithdrawal;

class MerchantWithdrawalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Merchant::all() as $merchant) {
            MerchantWithdrawal::factory()->create([
                'merchant_id' => $merchant->id
            ]);
        }
    }
}

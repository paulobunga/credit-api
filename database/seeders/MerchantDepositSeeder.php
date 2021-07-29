<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\ResellerBankCard;
use App\Models\MerchantDeposit;
use App\Models\TransactionMethod;

class MerchantDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Merchant::all() as $merchant) {
            $reseller_bank_card = ResellerBankCard::where('reseller_id', '!=', 1)
                ->inRandomOrder()->first();
            $deposit = MerchantDeposit::factory()->create([
                'merchant_id' => $merchant->id,
                'reseller_bank_card_id' => $reseller_bank_card->id,
            ]);
            $deposit->update([
                'status' => 2
            ]);
        }
    }
}

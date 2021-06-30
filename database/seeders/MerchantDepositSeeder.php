<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\ResellerBankCard;
use App\Models\MerchantDeposit;

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
            $reseller_bank_card = ResellerBankCard::inRandomOrder()->first();
            $deposit = MerchantDeposit::factory()->create([
                'merchant_id' => $merchant->id,
                'reseller_bank_card_id' => $reseller_bank_card->id
            ]);
            $deposit->transactions()->create([
                'transaction_method_id' => 1,
                'amount' => $deposit->amount
            ]);
            $deposit->transactions()->create([
                'transaction_method_id' => 5,
                'amount' => $deposit->amount * $deposit->merchant->transaction_fee
            ]);
        }
    }
}

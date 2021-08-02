<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\ResellerBankCard;
use App\Models\MerchantDeposit;
use App\Models\PaymentMethod;

class MerchantDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment_count = PaymentMethod::count();
        $card_count = ResellerBankCard::count()/$payment_count;
        foreach (Merchant::all() as $merchant) {
            $reseller_bank_card = ResellerBankCard::find($merchant->id % $card_count + $payment_count);
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

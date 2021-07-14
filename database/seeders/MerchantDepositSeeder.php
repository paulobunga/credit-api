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
        $methods = TransactionMethod::all()->pluck('id', 'name');
        foreach (Merchant::all() as $merchant) {
            $reseller_bank_card = ResellerBankCard::where('reseller_id', '!=', 1)
                ->inRandomOrder()->first();
            $deposit = MerchantDeposit::factory()->create([
                'merchant_id' => $merchant->id,
                'reseller_bank_card_id' => $reseller_bank_card->id,
                'status' => 2
            ]);
            // reseller
            $transaction = $deposit->transactions()->create([
                'transaction_method_id' => $methods['DEDUCT_CREDIT'],
                'amount' => $deposit->amount
            ]);
            $deposit->reseller->decrement('credit', $transaction->amount);
            $transaction = $deposit->transactions()->create([
                'transaction_method_id' => $methods['TOPUP_COIN'],
                'amount' => $deposit->amount * $deposit->reseller->transaction_fee
            ]);
            $deposit->reseller->increment('coin', $transaction->amount);
            // merchant
            $transaction = $deposit->transactions()->create([
                'transaction_method_id' => $methods['TOPUP_CREDIT'],
                'amount' => $deposit->amount
            ]);
            $deposit->reseller->increment('credit', $transaction->amount);
            $transaction = $deposit->transactions()->create([
                'transaction_method_id' => $methods['TRANSACTION_FEE'],
                'amount' => $deposit->amount * $deposit->merchant->transaction_fee
            ]);
            $deposit->reseller->decrement('credit', $transaction->amount);
        }
    }
}

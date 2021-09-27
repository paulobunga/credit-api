<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use App\Models\Merchant;
use App\Models\ResellerBankCard;
use App\Models\MerchantDeposit;
use App\Models\MerchantWithdrawal;
use App\Models\Reseller;
use App\Models\ResellerWithdrawal;
use App\Models\Transaction;

class MerchantDepositSeeder extends Seeder
{
    protected $faker;

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
        foreach (Merchant::all() as $merchant) {
            foreach ($merchant->credits as $credit) {
                $reseller_bank_card = ResellerBankCard::whereHas('reseller', function ($q) use ($credit) {
                    $q->where('currency', $credit->currency);
                })->inRandomOrder()->first();
                MerchantDeposit::create([
                    'merchant_order_id' => $merchant->id == 1 ? '9798223690986' : $this->faker->isbn13,
                    'method' => Arr::random($reseller_bank_card->PaymentChannel->payment_methods),
                    'amount' => 1000,
                    'currency' => $credit->currency,
                    'callback_url' => $this->faker->domainName . '/' . $this->faker->word,
                    'merchant_id' => $merchant->id,
                    'reseller_bank_card_id' => $reseller_bank_card->id,
                    'status' => MerchantDeposit::STATUS['APPROVED'],
                ]);
                MerchantWithdrawal::create([
                    'merchant_id' => $merchant->id,
                    'amount' => 100,
                    'currency' => $credit->currency,
                    'status' => MerchantWithdrawal::STATUS['APPROVED'],
                ]);
            }
        }
        foreach (Reseller::with('bankCards')->where('level', Reseller::LEVEL['RESELLER'])->get() as $reseller) {
            ResellerWithdrawal::create([
                'reseller_id' => $reseller->id,
                'reseller_bank_card_id' => $reseller->bankCards->random()->id,
                'audit_admin_id' => 1,
                'type' => ResellerWithdrawal::TYPE['COIN'],
                'transaction_type' => Transaction::TYPE['RESELLER_WITHDRAW_COIN'],
                'amount' => 1,
                'status' => ResellerWithdrawal::STATUS['APPROVED'],
                'extra' => [
                    'payment_type' => 'BY BANK TRANSFER',
                    'reason' => 'Withdraw',
                    'remark' => 'OK',
                    'memo' => 'success',
                    'creator' => $reseller->id
                ]
            ]);
        }
    }
}

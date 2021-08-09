<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\ResellerBankCard;
use App\Models\MerchantDeposit;
use App\Models\PaymentMethod;

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
        $payment_count = PaymentMethod::count();
        $card_count = ResellerBankCard::count() / $payment_count;
        foreach (Merchant::all() as $merchant) {
            $reseller_bank_card = ResellerBankCard::find($merchant->id % $card_count + $payment_count);
            MerchantDeposit::create([
                'merchant_order_id' => $this->faker->uuid,
                'account_no' => $this->faker->bankAccountNumber,
                'account_name' => $this->faker->name,
                'amount' => 100,
                'callback_url' => $this->faker->url,
                'reference_no' => $this->faker->numerify('N-########'),
                'merchant_id' => $merchant->id,
                'reseller_id' => $reseller_bank_card->reseller->id,
                'reseller_bank_card_id' => $reseller_bank_card->id,
                'status' => MerchantDeposit::STATUS['APPROVED'],
            ]);
        }
    }
}

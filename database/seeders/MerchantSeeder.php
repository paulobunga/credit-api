<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\MerchantCredit;
use App\Models\MerchantWhiteList;
use App\Settings\CurrencySetting;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(CurrencySetting $cs)
    {
        $merchant = Merchant::factory()->create([
            'uuid' => '224d4a1f-6fc5-4039-bd81-fcbc7f88c659',
            'name' => 'Test Merchant',
            'username' => 'merchant@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'callback_url' => 'http://google.com.tw',
            'status' => true,
        ]);
        $merchant->credits()->create([
            'currency' => $cs->types[0],
            'credit' => 0,
            'transaction_fee' => 0.001
        ]);
        $merchant = Merchant::factory()->create([
            'username' => 'merchant1@gmail.com',
        ]);
        $merchant->credits()->create([
            'currency' => $cs->types[1],
            'credit' => 0,
            'transaction_fee' => 0.001
        ]);
        $merchant = Merchant::factory()->create([
            'username' => 'merchant2@gmail.com',
        ]);
        $merchant->credits()->create([
            'currency' => $cs->types[0],
            'credit' => 0,
            'transaction_fee' => 0.001
        ]);
        $merchant->credits()->create([
            'currency' => $cs->types[1],
            'credit' => 0,
            'transaction_fee' => 0.001
        ]);

        foreach (Merchant::all() as $merchant) {
            MerchantWhiteList::factory()->create([
                'merchant_id' => $merchant->id
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Settings\CurrencySetting;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(CurrencySetting $c)
    {
        // create VND test merchant
        $merchant = Merchant::factory()->create([
            'uuid' => '224d4a1f-6fc5-4039-bd81-fcbc7f88c659',
            'api_key' => 'TCFTW2HtNqOtMmQMjSNh9TUMRxrM8l',
            'name' => 'Test Merchant',
            'username' => 'merchant@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'callback_url' => 'http://google.com.tw',
            'status' => true,
        ]);
        $merchant->credits()->create([
            'currency' => 'INR',
            'credit' => 0,
            'transaction_fee' => 0.001
        ]);
        $merchant->credits()->create([
            'currency' => 'VND',
            'credit' => 0,
            'transaction_fee' => 0.001
        ]);
        foreach (range(1, 2) as $i) {
            $merchant = Merchant::factory()->create([
                'username' => "merchant{$i}@gmail.com",
            ]);
            $count = 0;
            foreach ($c->currency as $currency => $v) {
                if ($count == $i) {
                    break;
                }
                $merchant->credits()->create([
                    'currency' => $currency,
                    'credit' => 0,
                    'transaction_fee' => $v['transaction_fee_percentage']
                ]);
                ++$count;
            }
        }
    }
}

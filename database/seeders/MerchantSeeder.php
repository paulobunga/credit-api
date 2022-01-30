<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
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
        // create multicurrency test merchant
        $merchant = Merchant::factory()->create([
            'uuid' => '224d4a1f-6fc5-4039-bd81-fcbc7f88c659',
            'api_key' => 'TCFTW2HtNqOtMmQMjSNh9TUMRxrM8l',
            'name' => 'Test Merchant',
            'username' => 'merchant@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'status' => true,
        ]);
        $merchant->whiteList()->create([
            'api' => ['172.19.0.1'],
            'backend' => ['172.19.0.1']
        ]);
        foreach ($c->currency as $currency => $setting) {
            $merchant->credits()->create([
                'currency' => $currency,
                'credit' => 0,
                'transaction_fee' => $setting['transaction_fee_percentage']
            ]);
            $merchant->assignTeams([
                'name' => 'Default',
                'type' => Team::TYPE['PAYIN'],
                'currency' => $currency,
            ], [
                'name' => 'Default',
                'type' => Team::TYPE['PAYOUT'],
                'currency' => $currency,
            ]);
        }
    }
}

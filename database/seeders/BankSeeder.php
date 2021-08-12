<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\PaymentChannel;
use App\Settings\CurrencySetting;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(CurrencySetting $setting)
    {
        Bank::factory()->count(11)->create();
        $channels = [
            'NETBANK' => [
                'methods' => [
                    PaymentChannel::METHOD['TRANSFER'],
                ],
                'currency' => ['IND', 'VND'],
            ],
            'UPI' => [
                'methods' => [
                    PaymentChannel::METHOD['QRCODE'],
                ],
                'currency' => ['IND'],
            ],
            'WALLET' => [
                'methods' => [
                    PaymentChannel::METHOD['TRANSFER'],
                ],
                'currency' => ['USDT'],
            ],
        ];
        foreach ($channels as $name => $ch) {
            foreach ($ch['currency'] as $currency) {
                PaymentChannel::create([
                    'name' => $name,
                    'payment_methods' => implode(',', $ch['methods']),
                    'banks' => implode(',', Bank::inRandomOrder()->limit(5)->pluck('id')->toArray()),
                    'currency' => $currency,
                    'status' => true,
                ]);
            }
        }
    }
}

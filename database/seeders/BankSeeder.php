<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\PaymentChannel;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::factory()->count(11)->create();
        $channels = [
            'NETBANK' => [
                'methods' => [
                    PaymentChannel::METHOD['TEXT'],
                ],
                'currency' => [
                    'IND' => Bank::where('currency', 'IND')->limit(3)->pluck('id')->toArray(),
                    'VND' => Bank::where('currency', 'VND')->limit(3)->pluck('id')->toArray(),
                ],
            ],
            'UPI' => [
                'methods' => [
                    PaymentChannel::METHOD['QRCODE'],
                ],
                'currency' => ['IND' => []],
            ],
            'WALLET' => [
                'methods' => [
                    PaymentChannel::METHOD['QRCODE'],
                ],
                'currency' => ['USDT' => []],
            ],
        ];
        foreach ($channels as $name => $ch) {
            foreach ($ch['currency'] as $currency => $banks) {
                PaymentChannel::create([
                    'name' => $name,
                    'payment_methods' => implode(',', $ch['methods']),
                    'banks' => implode(',', $banks),
                    'currency' => $currency,
                    'status' => true,
                ]);
            }
        }
    }
}

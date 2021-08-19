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
                'INR' => [
                    'banks' => Bank::where('currency', 'INR')->limit(3)->pluck('id')->toArray(),
                    'methods' => [
                        PaymentChannel::METHOD['TEXT'],
                    ],
                    'attributes' => ['account_number', 'account_name', 'ifsc_code']
                ],
                'VND' => [
                    'banks' => Bank::where('currency', 'VND')->limit(3)->pluck('id')->toArray(),
                    'methods' => [
                        PaymentChannel::METHOD['TEXT'],
                    ],
                    'attributes' => ['account_number', 'account_name', 'bank_name']
                ]
            ],
            'UPI' => [
                'INR' => [
                    'methods' => [
                        PaymentChannel::METHOD['TEXT'],
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['upi_id']
                ],
            ],
            'MOMOPAY' => [
                'VND' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['qrcode']
                ]
            ],
            'ZALOPAY' => [
                'VND' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['qrcode']
                ]
            ],
            'VIETTELPAY' => [
                'VND' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['qrcode']
                ]
            ],
        ];
        foreach ($channels as $name => $ch) {
            foreach ($ch as $currency => $s) {
                PaymentChannel::create([
                    'name' => $name,
                    'payment_methods' => implode(',', $s['methods']),
                    'attributes' => $s['attributes'],
                    'banks' => implode(',', $s['banks'] ?? []),
                    'currency' => $currency,
                    'status' => true,
                ]);
            }
        }
    }
}

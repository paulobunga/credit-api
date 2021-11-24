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
                    'attributes' => ['account_number', 'account_name', 'ifsc_code'],
                    'payin' => [
                        'sms_addresses' => []
                    ]
                ],
                'VND' => [
                    'banks' => Bank::where('currency', 'VND')->limit(3)->pluck('id')->toArray(),
                    'methods' => [
                        PaymentChannel::METHOD['TEXT'],
                    ],
                    'attributes' => ['account_number', 'account_name', 'bank_name'],
                    'payin' => [
                        'sms_addresses' => []
                    ]
                ]
            ],
            'UPI' => [
                'INR' => [
                    'methods' => [
                        PaymentChannel::METHOD['TEXT'],
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['upi_id'],
                    'payin' => [
                        'sms_addresses' => []
                    ]
                ],
            ],
            'BKASH' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number'],
                    'payin' => [
                        'sms_addresses' => ['bKash']
                    ]
                ],
            ],
            'NAGAD' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number'],
                    'payin' => [
                        'sms_addresses' => ['NAGAD']
                    ]
                ],
            ],
            'ROCKET' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number'],
                    'payin' => [
                        'sms_addresses' => ['16216']
                    ]
                ],
            ],
            'UPAY' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number'],
                    'payin' => [
                        'sms_addresses' => ['UPAY']
                    ]
                ],
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
                    'payin' => [
                        'status' => true,
                        'min' => 20,
                        'max' => 50000,
                        'sms_addresses' => $s['payin']['sms_addresses']
                    ],
                    'payout' => [
                        'status' => true,
                        'min' => 20,
                        'max' => 50000,
                        'auto_approval' => false
                    ]
                ]);
            }
        }
    }
}

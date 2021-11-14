<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\PaymentChannel;
use App\Models\ResellerBankCard;

class ResellerBankCardSeeder extends Seeder
{
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
        foreach (Reseller::where('level', Reseller::LEVEL['RESELLER'])->get() as $reseller) {
            foreach (PaymentChannel::where('currency', $reseller->currency)->get() as $ch) {
                $card = new ResellerBankCard([
                    'reseller_id' => $reseller->id,
                    'payment_channel_id' => $ch->id
                ]);
                $card->status = ResellerBankCard::STATUS['ACTIVE'];
                switch ($ch->name) {
                    case 'NETBANK':
                        $attributes = [
                            'account_number' => $this->faker->unique()->bankAccountNumber,
                            'account_name' => $this->faker->name,
                        ];
                        if ($card->reseller->currency == 'VND') {
                            $attributes['bank_name'] = $this->faker->name . ' bank';
                        } elseif ($card->reseller->currency == 'INR') {
                            $attributes['ifsc_code'] = $this->faker->unique()->numerify('###########');
                        }
                        break;
                    case 'UPI':
                        $attributes = [
                            'upi_id' =>  Str::random(15) . '@upi'
                        ];
                        break;
                    case 'VIETTELPAY':
                    case 'ZALOPAY':
                    case 'MOMOPAY':
                        $attributes = [
                            'qrcode' =>  'https://google.com.tw'
                        ];
                        break;
                    case 'BKASH':
                    case 'NAGAD':
                    case 'UPAY':
                        $attributes = [
                            'wallet_number' => $this->faker->numerify('01#########')
                        ];
                        break;
                    case 'ROCKET':
                        $attributes = [
                            'wallet_number' => $this->faker->numerify('01##########')
                        ];
                        break;
                    default:
                        $attributes = [];
                }
                $card->attributes = $attributes;
                $card->save();
            }
        }
    }
}

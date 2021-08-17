<?php

namespace Database\Seeders;

use \Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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
                $card = ResellerBankCard::factory()->make([
                    'bank_id' => $ch->banks->isEmpty() ?
                        0 : Arr::random($ch->banks->pluck('id')->toArray()),
                    'reseller_id' => $reseller->id,
                    'payment_channel_id' => $ch->id
                ]);
                $card->status = $reseller->name == 'Test Reseller' ? 1 : 0;
                switch ($ch->name) {
                    case 'UPI':
                        $card->account_name = '';
                        $card->account_no = Str::random(40) . '@upi';
                        break;
                    case 'WALLET':
                        $card->account_name = '';
                        $card->account_no = 'bc' . Str::random(40);
                        break;
                }
                $card->save();
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\Bank;
use App\Models\ResellerBankCard;
use Illuminate\Support\Str;

class ResellerBankCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $methods = \App\Models\PaymentMethod::all();
        foreach (Reseller::where('level', 3)->get() as $reseller) {
            foreach ($methods as $method) {
                $bank = Bank::where('payment_method_id', $method->id)->inRandomOrder()->first();
                $card = ResellerBankCard::factory()->make([
                    'bank_id' => $bank->id,
                    'reseller_id' => $reseller->id,
                ]);
                if ($reseller->name == 'Test Reseller') {
                    $card->status = true;
                } else {
                    $card->status = false;
                }
                switch ($method->name) {
                    case 'online_bank':
                        break;
                    case 'upi':
                        $card->account_name = '';
                        $card->account_no = Str::random(40) . '@upi';
                        break;
                    case 'wallet':
                        $card->account_name = '';
                        $card->account_no = 'bc' . Str::random(40);
                        break;
                }
                $card->save();
            }
        }
    }
}

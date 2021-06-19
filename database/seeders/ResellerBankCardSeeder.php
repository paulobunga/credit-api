<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\Bank;
use App\Models\ResellerBankCard;

class ResellerBankCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Reseller::all() as $reseller) {
            $bank = Bank::inRandomOrder()->first();
            ResellerBankCard::factory()->create([
                'bank_id' => $bank->id,
                'reseller_id' => $reseller->id,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\MerchantWhiteList;

class WhiteListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Merchant::all() as $merchant) {
            MerchantWhiteList::factory()->count(2)->create([
                'merchant_id' => $merchant->id
            ]);
        }
    }
}

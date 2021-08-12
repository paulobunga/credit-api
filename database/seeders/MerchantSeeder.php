<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\MerchantWhiteList;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Merchant::factory()->create([
            'uuid' => '224d4a1f-6fc5-4039-bd81-fcbc7f88c659',
            'name' => 'Test Merchant',
            'username' => 'merchant@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'credit' => 20000,
            'callback_url' => 'http://google.com.tw',
            'status' => true,
        ]);
        Merchant::factory()->count(3)->create([
            'credit' => 3000,
        ]);
        foreach (Merchant::all() as $merchant) {
            MerchantWhiteList::factory()->create([
                'merchant_id' => $merchant->id
            ]);
        }
    }
}

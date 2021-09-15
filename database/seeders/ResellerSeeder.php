<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\Transaction;
use App\Models\ResellerDeposit;
use App\Settings\AgentSetting;
use App\Settings\CurrencySetting;
use App\Settings\ResellerSetting;

class ResellerSeeder extends Seeder
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
    public function run(
        ResellerSetting $rs,
        AgentSetting $as,
        CurrencySetting $c
    ) {
        \App\Models\Reseller::create([
            'upline_id' => Reseller::where('currency', 'VND')->first()->id,
            'level' => Reseller::LEVEL['RESELLER'],
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'currency' => 'VND',
            'credit' => 0,
            'coin' => 0,
            'pending_limit' => $rs->default_pending_limit,
            'commission_percentage' => $c->getCommissionPercentage('VND', Reseller::LEVEL['RESELLER']),
            'downline_slot' => 0,
            'status' => true,
        ]);
        // create INR 4 level agent
        foreach ($c->currency as $currency => $setting) {
            $level = Reseller::LEVEL['RESELLER'];
            Reseller::factory()->create([
                'username' => "{$currency}Reseller@gmail.com",
                'upline_id' => Reseller::where('currency', $currency)->first()->id,
                'level' =>  $level,
                'currency' => $currency,
                'credit' => 0,
                'pending_limit' => $rs->getDefaultPendingLimit($level),
                'commission_percentage' => $c->getCommissionPercentage($currency, $level),
                'downline_slot' => $as->getDefaultDownLineSlot($level),
                'status' => Reseller::STATUS['ACTIVE']
            ]);
        }
        // create reseller deposit
        foreach (Reseller::where('level', Reseller::LEVEL['RESELLER'])->get() as $reseller) {
            ResellerDeposit::create([
                'reseller_id' => $reseller->id,
                'audit_admin_id' => 1,
                'type' => ResellerDeposit::TYPE['CREDIT'],
                'transaction_type' => Transaction::TYPE['RESELLER_TOPUP_CREDIT'],
                'amount' => 10000,
                'status' => ResellerDeposit::STATUS['APPROVED'],
                'extra' => [
                    'payment_type' => 'By Cash'
                ]
            ]);
        }
    }
}

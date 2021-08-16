<?php

namespace Database\Seeders;

use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Models\Transaction;
use App\Models\ResellerDeposit;
use App\Models\ResellerWithdrawal;
use App\Settings\AgentSetting;
use App\Settings\CommissionSetting;
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
        CommissionSetting $cs,
        AgentSetting $as,
        CurrencySetting $currency
    ) {
        $reseller = \App\Models\Reseller::create([
            'upline_id' => 1,
            'level' => Reseller::LEVEL['AGENT_MASTER'],
            'name' => 'Test Master Agent',
            'username' => 'master@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'currency' => $currency->types[0],
            'credit' => 0,
            'coin' => 0,
            'pending_limit' => 0,
            'commission_percentage' => $cs->master_agent_percentage,
            'downline_slot' => 1,
            'status' => true,
        ]);
        \App\Models\Reseller::create([
            'upline_id' => $reseller->id,
            'level' => Reseller::LEVEL['RESELLER'],
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'currency' => $currency->types[0],
            'credit' => 0,
            'coin' => 0,
            'pending_limit' => $rs->default_pending_limit,
            'commission_percentage' => $cs->reseller_percentage,
            'downline_slot' => 0,
            'status' => true,
        ]);
        foreach (Reseller::LEVEL as $level) {
            $reseller = Reseller::factory()->create([
                'username' => "reseller{$level}@gmail.com",
                'upline_id' => $level ? $reseller->id : 0,
                'level' => $level,
                'currency' => $currency->types[1],
                'credit' => 0,
                'pending_limit' => $rs->getDefaultPendingLimit($level),
                'commission_percentage' => $cs->getDefaultPercentage($level),
                'downline_slot' => $as->getDefaultDownLineSlot($level),
                'status' => Reseller::STATUS['ACTIVE']
            ]);
        }
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

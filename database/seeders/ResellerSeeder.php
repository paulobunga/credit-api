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
        // create 4 level agent
        foreach ($c->currency as $currency => $setting) {
            foreach (Reseller::LEVEL as $level) {
                $r = Reseller::create([
                    'name' => $this->getName($currency, $level),
                    'username' => $this->getUserName($currency, $level),
                    'phone' => $this->faker->phoneNumber,
                    'upline_id' => $level == Reseller::LEVEL['REFERRER'] ? 0 : $r->id,
                    'uplines' => $level == Reseller::LEVEL['REFERRER'] ? [] : array_merge($r->uplines, [$r->id]),
                    'level' =>  $level,
                    'currency' => $currency,
                    'credit' => 0,
                    'payin' => [
                        'commission_percentage' => $c->getCommissionPercentage($currency, $level),
                        'pending_limit' => $rs->getDefaultPendingLimit($level),
                        'status' => true
                    ],
                    'payout' => [
                        'commission_percentage' => $c->getCommissionPercentage($currency, $level),
                        'pending_limit' => $rs->getDefaultPendingLimit($level),
                        'status' => true
                    ],
                    'downline_slot' => $as->getDefaultDownLineSlot($level),
                    'status' => Reseller::STATUS['ACTIVE'],
                    'password' => 'P@ssw0rd',
                ]);
            }
        }
        $vnd_agent = Reseller::where([
            'currency' => 'VND',
            'level' => Reseller::LEVEL['AGENT']
        ])->first();
        // create test VND
        \App\Models\Reseller::create([
            'upline_id' => $vnd_agent->id,
            'uplines' => array_merge($vnd_agent->uplines, [$vnd_agent->id]),
            'level' => Reseller::LEVEL['RESELLER'],
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'currency' => 'VND',
            'credit' => 0,
            'coin' => 0,
            'payin' => [
                'commission_percentage' => $c->getCommissionPercentage('VND', Reseller::LEVEL['RESELLER']),
                'pending_limit' => $rs->default_pending_limit,
                'status' => true
            ],
            'payout' => [
                'commission_percentage' => $c->getCommissionPercentage('VND', Reseller::LEVEL['RESELLER']),
                'pending_limit' => $rs->default_pending_limit,
                'status' => true,
            ],
            'downline_slot' => 0,
            'status' => true,
        ]);
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
                    'payment_type' => 'By Cash',
                    'reason' => 'Deposit',
                    'remark' => 'OK',
                    'memo' => 'OK',
                    'creator' => 1
                ]
            ]);
        }
    }

    protected function getName($currency, $level)
    {
        $name = '';
        switch ($level) {
            case Reseller::LEVEL['REFERRER']:
                $name = 'Home';
                break;
            case Reseller::LEVEL['AGENT_MASTER']:
                $name = 'Super Agent';
                break;
            case Reseller::LEVEL['AGENT']:
                $name = 'Master Agent';
                break;
            case Reseller::LEVEL['RESELLER']:
                $name = 'Agent';
                break;
        }
        return $currency . ' ' . $name;
    }

    protected function getUserName($currency, $level)
    {
        switch ($level) {
            case Reseller::LEVEL['REFERRER']:
                return  "{$currency}_home@gmail.com";
            case Reseller::LEVEL['AGENT_MASTER']:
                return  "{$currency}_super_agent@gmail.com";
            case Reseller::LEVEL['AGENT']:
                return  "{$currency}_master_agent@gmail.com";
            default:
                return  "{$currency}_agent@gmail.com";
        }
    }
}

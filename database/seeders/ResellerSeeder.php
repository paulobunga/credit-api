<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
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
        CurrencySetting $c,
        Reseller $agent
    ) {
        // create 4 level agent
        foreach ($c->currency as $currency => $setting) {
            foreach (Reseller::LEVEL as $level) {
                $agent = Reseller::create([
                    'name' => $this->getName($currency, $level),
                    'username' => $this->getUserName($currency, $level),
                    'phone' => $this->faker->phoneNumber,
                    'upline_id' => $level == Reseller::LEVEL['HOUSE'] ? 0 : $agent->id,
                    'uplines' => $level == Reseller::LEVEL['HOUSE'] ?
                        [] :
                        array_merge($agent->uplines, [$agent->id]),
                    'level' =>  $level,
                    'currency' => $currency,
                    'payin' => [
                        'commission_percentage' => $c->getCommissionPercentage($currency, $level),
                        'pending_limit' => $rs->getDefaultPendingLimit($level),
                        'status' => true,
                        'auto_sms_approval' => false,
                        'max' => $setting['payin']['max'],
                        'min' => $setting['payin']['min']
                    ],
                    'payout' => [
                        'commission_percentage' => $c->getCommissionPercentage($currency, $level),
                        'pending_limit' => $rs->getDefaultPendingLimit($level),
                        'status' => true,
                        'daily_amount_limit' => 50000,
                        'max' => $setting['payin']['max'],
                        'min' => $setting['payin']['min']
                    ],
                    'downline_slot' => $as->getDefaultDownLineSlot($level),
                    'status' => Reseller::STATUS['ACTIVE'],
                    'password' => 'P@ssw0rd',
                ]);
            }
        }
        $setting = $c->currency['VND'];
        $vnd_agent = Reseller::where([
            'currency' => 'VND',
            'level' => Reseller::LEVEL['MASTER_AGENT']
        ])->first();
        // create test VND
        $agent = \App\Models\Reseller::create([
            'upline_id' => $vnd_agent->id,
            'uplines' => array_merge($vnd_agent->uplines, [$vnd_agent->id]),
            'level' => Reseller::LEVEL['AGENT'],
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'currency' => 'VND',
            'payin' => [
                'commission_percentage' => $c->getCommissionPercentage('VND', Reseller::LEVEL['AGENT']),
                'pending_limit' => $rs->default_pending_limit,
                'status' => true,
                'auto_sms_approval' => false,
                'max' => $setting['payin']['max'],
                'min' => $setting['payin']['min']
            ],
            'payout' => [
                'commission_percentage' => $c->getCommissionPercentage('VND', Reseller::LEVEL['AGENT']),
                'pending_limit' => $rs->default_pending_limit,
                'status' => true,
                'daily_amount_limit' => 50000,
                'max' => $setting['payin']['max'],
                'min' => $setting['payin']['min']
            ],
            'downline_slot' => 0,
            'status' => true,
        ]);

        // create reseller deposit
        foreach (Reseller::where('level', Reseller::LEVEL['AGENT'])->get() as $reseller) {
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
        $currency = strtolower($currency);
        switch ($level) {
            case Reseller::LEVEL['HOUSE']:
                $name = 'House';
                break;
            case Reseller::LEVEL['SUPER_AGENT']:
                $name = 'Super Agent';
                break;
            case Reseller::LEVEL['MASTER_AGENT']:
                $name = 'Master Agent';
                break;
            case Reseller::LEVEL['AGENT']:
                $name = 'Agent';
                break;
        }
        return $currency . ' ' . $name;
    }

    protected function getUserName($currency, $level)
    {
        $currency = strtolower($currency);
        switch ($level) {
            case Reseller::LEVEL['HOUSE']:
                return  "{$currency}_house@gmail.com";
            case Reseller::LEVEL['SUPER_AGENT']:
                return  "{$currency}_super_agent@gmail.com";
            case Reseller::LEVEL['MASTER_AGENT']:
                return  "{$currency}_master_agent@gmail.com";
            default:
                return  "{$currency}_agent@gmail.com";
        }
    }
}

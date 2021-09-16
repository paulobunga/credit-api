<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use App\Models\Reseller;

class CreateCurrencySettings extends SettingsMigration
{
    public function up(): void
    {
        $currency =  [
            'VND' => [
                'referrer_percentage' => 0,
                'master_agent_percentage' => 0.003,
                'agent_percentage' => 0.004,
                'reseller_percentage' => 0.005,
                'transaction_fee_percentage' => 0.001,
                'expired_minute' => 5,
            ],
            'INR' => [
                'referrer_percentage' => 0,
                'master_agent_percentage' => 0.003,
                'agent_percentage' => 0.004,
                'reseller_percentage' => 0.005,
                'transaction_fee_percentage' => 0.001,
                'expired_minute' => 5,
            ]
        ];
        $this->migrator->add('currency.currency', $currency);
        foreach ($currency as $c => $v) {
            // create default referrer
            $r = Reseller::create([
                'level' => 0,
                'name' => "{$c} House",
                'username' => "{$c}House@gmail.com",
                'phone' => '0978475446',
                'currency' => $c,
                'password' => 'P@ssw0rd',
                'status' => Reseller::STATUS['ACTIVE'],
            ]);
            // create default super agent
            $r = Reseller::create([
                'level' => 1,
                'upline_id' => $r->id,
                'name' => "{$c} Super Agent",
                'username' => "{$c}SuperAgent@gmail.com",
                'phone' => '0978475446',
                'currency' => $c,
                'password' => 'P@ssw0rd',
                'status' => Reseller::STATUS['ACTIVE'],
            ]);
            // create default master agent
            Reseller::create([
                'level' => 2,
                'upline_id' => $r->id,
                'name' => "{$c} Master Agent",
                'username' => "{$c}MasterAgent@gmail.com",
                'phone' => '0978475446',
                'currency' => $c,
                'password' => 'P@ssw0rd',
                'status' => Reseller::STATUS['ACTIVE'],
            ]);
        }
    }
}

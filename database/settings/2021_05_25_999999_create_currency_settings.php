<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateCurrencySettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('currency.currency', [
            'VND' => [
                'referrer_percentage' => 0,
                'master_agent_percentage' => 0.003,
                'agent_percentage' => 0.004,
                'reseller_percentage' => 0.005,
                'transaction_fee_percentage' => 0.001,
            ],
            'INR' => [
                'referrer_percentage' => 0,
                'master_agent_percentage' => 0.003,
                'agent_percentage' => 0.004,
                'reseller_percentage' => 0.005,
                'transaction_fee_percentage' => 0.001,
            ]
        ]);
    }
}

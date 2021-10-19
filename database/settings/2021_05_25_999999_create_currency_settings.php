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
                'expired_minutes' => 5,
            ],
            'INR' => [
                'referrer_percentage' => 0,
                'master_agent_percentage' => 0.003,
                'agent_percentage' => 0.004,
                'reseller_percentage' => 0.005,
                'transaction_fee_percentage' => 0.001,
                'expired_minutes' => 5,
            ]
        ];
        $this->migrator->add('currency.currency', $currency);
    }
}

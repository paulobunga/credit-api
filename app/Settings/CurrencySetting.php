<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use App\Models\Reseller;

class CurrencySetting extends Settings
{
    public array $currency;

    public static function group(): string
    {
        return 'currency';
    }

    public function getCommissionPercentage($currency, $level)
    {
        $c = $this->currency[$currency] ?? null;
        if (!isset($c)) {
            return 0;
        }
        switch ($level) {
            case Reseller::LEVEL['HOME']:
                return $c['referrer_percentage'];
            case Reseller::LEVEL['SUPER_AGENT']:
                return $c['master_agent_percentage'];
            case Reseller::LEVEL['MASTER_AGENT']:
                return $c['agent_percentage'];
            case Reseller::LEVEL['AGENT']:
                return $c['reseller_percentage'];
        }
        return 0;
    }

    public function getExpiredMinutes($currency)
    {
        $c = $this->currency[$currency] ?? null;
        return isset($c) ? $c['expired_minutes'] : 0;
    }
}

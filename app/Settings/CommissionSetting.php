<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use App\Models\Reseller;

class CommissionSetting extends Settings
{
    public float $referrer_percentage;

    public float $master_agent_percentage;

    public float $agent_percentage;

    public float $reseller_percentage;

    public float $total_percentage;

    public static function group(): string
    {
        return 'commission';
    }

    public function getDefaultPercentage($level)
    {
        switch ($level) {
            case Reseller::LEVEL['referrer']:
                return $this->referrer_percentage;
            case Reseller::LEVEL['master agent']:
                return $this->master_agent_percentage;
            case Reseller::LEVEL['agent']:
                return $this->agent_percentage;
            case Reseller::LEVEL['reseller']:
                return $this->reseller_percentage;
            default:
                return 0;
        }
    }
}

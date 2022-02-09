<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use App\Models\Reseller;

class AgentSetting extends Settings
{
    public int $default_downline_slot;

    public int $max_downline_slot;

    public static function group(): string
    {
        return 'agent';
    }

    public function getDefaultDownLineSlot($level)
    {
        switch ($level) {
            case Reseller::LEVEL['SUPER_AGENT']:
            case Reseller::LEVEL['MASTER_AGENT']:
                return $this->default_downline_slot;
            default:
                return 0;
        }
    }
}

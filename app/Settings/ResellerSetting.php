<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use App\Models\Reseller;

class ResellerSetting extends Settings
{
    public int $default_pending_limit;

    public int $max_pending_limit;

    public static function group(): string
    {
        return 'reseller';
    }

    public function getDefaultPendingLimit($level)
    {
        switch ($level) {
            case Reseller::LEVEL['AGENT']:
                return $this->default_pending_limit;
            default:
                return 0;
        }
    }
}

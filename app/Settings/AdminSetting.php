<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AdminSetting extends Settings
{
    public array $white_lists;

    public int $expired_payin_limit_notify;
    
    public static function group(): string
    {
        return 'admin';
    }
}

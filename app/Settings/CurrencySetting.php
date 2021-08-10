<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CurrencySetting extends Settings
{
    public array $types;

    public static function group(): string
    {
        return 'currency';
    }
}

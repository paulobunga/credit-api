<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateCurrencySettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('currency.types', ['VND', 'IND']);
    }
}

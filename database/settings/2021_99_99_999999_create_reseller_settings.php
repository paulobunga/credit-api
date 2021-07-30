<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateResellerSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('reseller.default_pending_limit', 5);
        $this->migrator->add('reseller.max_pending_limit', 10);
    }
}

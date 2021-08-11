<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateAdminSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('admin.white_lists', []);
    }
}

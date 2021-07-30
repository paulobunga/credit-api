<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateAgentSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('agent.default_downline_slot', 20);
        $this->migrator->add('agent.max_downline_slot', 100);
    }
}

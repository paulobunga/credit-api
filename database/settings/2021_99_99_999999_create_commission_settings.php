<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateCommissionSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('commission.referrer_percentage', 0);
        $this->migrator->add('commission.master_agent_percentage', 0.0003);
        $this->migrator->add('commission.agent_percentage', 0.0004);
        $this->migrator->add('commission.reseller_percentage', 0.0005);
        $this->migrator->add('commission.total_percentage', 0.0012);
    }
}

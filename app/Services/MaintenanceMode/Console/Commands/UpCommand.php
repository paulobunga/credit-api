<?php

namespace App\Services\MaintenanceMode\Console\Commands;

class UpCommand extends MaintenanceCommand
{
    /**
     * @var string
     */
    protected $name = 'up';

    /**
     * @var string
     */
    protected $description = 'Bring the application out of maintenance mode.';

    /**
     * Bring the application out of maintenance mode.
     */
    public function handle()
    {
        if ($this->maintenance->isDownMode()) {
            $this->setUpMode();
        } else {
            $this->info('The application was already alive.');
        }
    }
}

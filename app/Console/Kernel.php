<?php

namespace App\Console;

use Laravel\Lumen\Application;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Services\MaintenanceMode\Console\Commands\DownCommand::class,
        \App\Services\MaintenanceMode\Console\Commands\UpCommand::class,
        \App\Console\Commands\ReportDaily::class,
        \App\Console\Commands\PermissionList::class,
        \App\Console\Commands\CheckActivate::class,
        \App\Console\Commands\CheckCashIn::class,
        \App\Console\Commands\CheckOnline::class,
        \App\Console\Commands\ExecuteSql::class,
        \App\Console\Commands\ImportFile::class,
        \App\Console\Commands\AutoApproval::class,
    ];

    public function __construct(Application $app)
    {
        parent::__construct($app);
        if (class_exists(\Knuckles\Scribe\Commands\GenerateDocumentation::class)) {
            $this->commands[] = \Knuckles\Scribe\Commands\GenerateDocumentation::class;
        }
        if (class_exists(\Knuckles\Scribe\Commands\MakeStrategy::class)) {
            $this->commands[] = \Knuckles\Scribe\Commands\MakeStrategy::class;
        }
        if (class_exists(\Knuckles\Scribe\Commands\Upgrade::class)) {
            $this->commands[] = \Knuckles\Scribe\Commands\Upgrade::class;
        }
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        # Generate daily report
        $schedule->command('report:daily')
            ->name('ReportDaily')
            ->runInBackground()
            ->onOneServer()
            ->everyTenMinutes();
        # check Activate code
        $schedule->command('check:activate')
            ->name('CheckActivate')
            ->runInBackground()
            ->onOneServer()
            ->everyMinute();
        # Check payin orders
        $schedule->command('check:cashin')
            ->name('CheckCashIn')
            ->runInBackground()
            ->onOneServer()
            ->everyMinute();
        # Check Online status
        $schedule->command('check:online')
            ->name('CheckOnline')
            ->runInBackground()
            ->onOneServer()
            ->everyMinute();
        # Auto approve payout orders
        $schedule->command('auto:approval')
            ->name('AutoApproval')
            ->runInBackground()
            ->onOneServer()
            ->everyMinute();
    }
}

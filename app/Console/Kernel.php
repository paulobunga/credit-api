<?php

namespace App\Console;

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
        \App\Console\Commands\ReportDailyCommand::class,
        \App\Console\Commands\PermissionListCommand::class,
        \App\Console\Commands\ActivateCheckCommand::class,
    ];

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
            ->dailyAt('02:00');
        # Activate code check
        $schedule->command('activate:check')
            ->name('ActivateCheck')
            ->runInBackground()
            ->everyMinute();
    }
}

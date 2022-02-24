<?php

namespace App\Services\MaintenanceMode\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MaintenanceMode\Http\Middleware\MaintenanceModeMiddleware;
use App\Services\MaintenanceMode\Console\Commands\DownCommand;
use App\Services\MaintenanceMode\Console\Commands\UpCommand;
use App\Services\MaintenanceMode\MaintenanceModeService;

class MaintenanceServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     */
    public function register()
    {
        app()->middleware([
            MaintenanceModeMiddleware::class,
        ]);

        $this->app->singleton('maintenance', function () {
            return new MaintenanceModeService(app());
        });

        $this->app->singleton('command.up', function () {
            return new UpCommand($this->app['maintenance']);
        });

        $this->app->singleton('command.down', function () {
            return new DownCommand($this->app['maintenance']);
        });

        $this->commands(['command.up', 'command.down']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.up', 'command.down'];
    }
}

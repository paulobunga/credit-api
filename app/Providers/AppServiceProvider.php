<?php

namespace App\Providers;

use Knuckles\Scribe\Scribe;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // register custom serializer
        if ($manager = app('api.transformer')->getFractal()) {
            $manager->setSerializer(new \App\Transformers\Serializer\DataArraySerializer());
        }
        $this->app->singleton('blade.compiler', function () {
            return new BladeCompiler(
                $this->app['files'],
                config('view.compiled')
            );
        });
        $this->app->singleton('pusher', function () {
            return app('Illuminate\Broadcasting\BroadcastManager')->driver('pusher')->getPusher();
        });
        $this->app->singleton('settings.currency', function () {
            return app(\App\Settings\CurrencySetting::class);
        });
        // register custom morph type
        Relation::morphMap([
            'admin' => 'App\Models\Admin',
            'reseller' => 'App\Models\Reseller',
            'merchant' => 'App\Models\Merchant',
            'merchant.deposit' => 'App\Models\MerchantDeposit',
            'merchant.withdrawal' => 'App\Models\MerchantWithdrawal',
            'merchant.settlement' => 'App\Models\MerchantSettlement',
            'reseller.deposit' => 'App\Models\ResellerDeposit',
            'reseller.withdrawal' => 'App\Models\ResellerWithdrawal',
        ]);
    }

    public function boot()
    {
        app()->routeMiddleware([
            'api.auth' => \App\Http\Middleware\Authenticate::class,
        ]);
        if (class_exists(\Knuckles\Scribe\Scribe::class)) {
            Scribe::afterGenerating(function (array $paths) {
                // Move the files, upload to S3, etc...
                copy('public/favicon.ico', 'docs/favicon.ico');
            });
        }
        $this->bootBladeComponents();
        $this->registerValidationRules();
    }

    protected function bootBladeComponents()
    {
        Blade::component('x-header', \App\View\Components\Header::class);
        Blade::component('x-stepper', \App\View\Components\Stepper::class);
        Blade::component('x-alert', \App\View\Components\Alert::class);
        Blade::component('x-timer', \App\View\Components\Timer::class);
    }

    protected function registerValidationRules()
    {
        Validator::extend('currency', '\App\Rules\Currency@passes');
        Validator::extend('keysin', function ($attribute, $value, $parameters, $validator) {
            return (new \App\Rules\KeysIn($parameters))->passes($attribute, $value);
        });
    }
}

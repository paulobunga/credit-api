<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        // register custom morph type
        Relation::morphMap([
            'admin' => 'App\Models\Admin',
            'merchant.deposit' => 'App\Models\MerchantDeposit',
            'merchant.withdrawal' => 'App\Models\MerchantWithdrawal',
            'reseller.deposit' => 'App\Models\ResellerDeposit',
            'reseller.withdrawal' => 'App\Models\ResellerWithdrawal',
        ]);
    }

    public function boot()
    {
        $this->app->routeMiddleware([
            'api.auth' => \App\Http\Middleware\Authenticate::class,
        ]);
        $this->bootBroadCast();
    }

    protected function bootBroadCast()
    {
        $router = app('router');
        $router->get('/broadcasting/auth', ['uses' => '\App\Http\Controllers\BroadcastController@authenticate']);
        $router->post('/broadcasting/auth', ['uses' => '\App\Http\Controllers\BroadcastController@authenticate']);
    }
}

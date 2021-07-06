<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($adapter = app('api.transformer')->getAdapter()) {
            $manager = $adapter->getFractal();
            $manager->setSerializer(new \App\Transformers\Serializer\DataArraySerializer());
        }
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

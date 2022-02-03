<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Policies\RolePolicy;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $abilities = [
            'index' => 'index',
            'show' => 'show',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete',
        ];
        Gate::resource('admin.roles', RolePolicy::class, $abilities);
    }
}

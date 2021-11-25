<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Log\Events\MessageLogged;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    public function register()
    {
        Log::listen(function (MessageLogged $msg) {
            \App\Models\Log::create([
                'message'       => $msg->message,
                'channel'       => Log::getName(),
                'level'         => $msg->level,
                'context'       => json_encode($msg->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            ]);
        });
    }
}

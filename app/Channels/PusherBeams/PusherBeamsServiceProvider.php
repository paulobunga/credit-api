<?php

namespace App\Channels\PusherBeams;

use Illuminate\Support\ServiceProvider;
use Pusher\PushNotifications\PushNotifications;

class PusherBeamsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->when(PusherBeams::class)
            ->needs(PushNotifications::class)
            ->give(function () {
                $config = config('broadcasting.connections.beams');

                return new PushNotifications([
                    'secretKey' => $config['secret_key'],
                    'instanceId' => $config['instance_id'],
                ]);
            });
    }
}

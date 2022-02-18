<?php

namespace App\Notifications\Admin;

use App\Channels\OneSignal;
use App\Notifications\Base as BaseNotification;

abstract class Base extends BaseNotification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return \App\Channels\PusherBeams\PusherMessage
     */
    public function via($notifiable)
    {
        return [
            'database'
        ];
    }
}

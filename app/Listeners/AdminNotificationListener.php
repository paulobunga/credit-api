<?php

namespace App\Listeners;

use App\Events\AdminNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Channels\OneSignal;

class AdminNotificationListener
{

    protected $oneSignal;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->oneSignal = new OneSignal();
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AdminNotification  $event
     * @return void
     */
    public function handle(AdminNotification $event)
    {
        $admins = \App\Models\Admin::all();
        Notification::send($admins, $event->model);

        $this->oneSignal->send(
            $admins->first(),
            $event->model,
            [
                "model" => "admin",
                "platform" => "web",
                "targeting" => [
                    "tags" => [
                        [
                            "key" => "subscription_topic",
                            "relation" => "=",
                            "value" => "admin_notifications"
                        ]
                    ]
                ]
            ]
        );
    }
}

<?php

namespace App\Listeners;

use App\Events\AdminNotification;
use Illuminate\Support\Facades\Notification;

class AdminNotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
    }
}

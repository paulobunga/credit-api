<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AdminNotification extends Event implements ShouldBroadcast
{
    public $message;
    public $model;
     /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $model)
    {
        $this->message = $message;
        $this->model = $model;
    }

    public function broadcastWith()
    {
        return $this->message;
    }

    public function broadcastAs()
    {
        return 'admin.notifications';
    }

    public function broadcastOn()
    {
        return [ new PrivateChannel('App.Models.Admin.Notify') ];
    }
}

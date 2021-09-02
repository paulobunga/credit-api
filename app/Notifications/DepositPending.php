<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class DepositPending extends Notification implements ShouldBroadcast
{
    use Queueable;

    public Model $deposit;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Model $deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function broadcastType()
    {
        return 'notifications.deposit';
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->deposit->id,
            'message' => $this->deposit->merchant_order_id,
            'time' => $this->deposit->updated_at->toDateTimeString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onQueue('echo');
    }
}

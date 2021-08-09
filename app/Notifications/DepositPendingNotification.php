<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class DepositPendingNotification extends Notification implements ShouldBroadcast
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
            'message' => str_replace(
                ['\r', '\n'],
                '',
                "You got a new order {$this->deposit->order_id},
                please check your account {$this->deposit->resellerBankCard->account_no} with the following info,
                account number {$this->deposit->account_no},
                account name {$this->deposit->account_name},
                amount {$this->deposit->amount},
                "
            ),
            'time' => $this->deposit->updated_at->toDateTimeString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onQueue('echo');
    }
}

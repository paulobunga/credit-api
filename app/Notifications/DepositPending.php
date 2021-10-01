<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Channels\PusherBeams\PusherBeams;
use App\Channels\PusherBeams\PusherMessage;

class DepositPending extends Notification
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
        return [
            'database',
            'broadcast',
            PusherBeams::class
        ];
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
            // 'id' => $this->deposit->id,
            'title' => 'New Deposit',
            'body' => "You got a new Pending, {$this->deposit->merchant_order_id}",
            "icon" => '/icons/favicon-96x96.png',
        ];
    }

    /**
     * Notification message for IOS, Android, Web
     *
     * @param  mixed $notifiable
     * @return void
     */
    public function toPushNotification($notifiable)
    {
        $data = $this->toArray($notifiable);

        return PusherMessage::create()
            ->web()
            ->badge(1)
            ->title($data['title'])
            ->body($data['body']);
    }
    
    /**
     * Websocket message
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage $message
     */
    public function toBroadcast($notifiable)
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onQueue('echo');
    }
}

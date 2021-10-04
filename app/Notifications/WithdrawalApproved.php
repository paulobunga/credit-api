<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Channels\PusherBeams\PusherBeams;
use App\Channels\PusherBeams\PusherMessage;

class WithdrawalApproved extends Notification implements ShouldBroadcast
{
    use Queueable;

    public Model $m;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Model $m)
    {
        $this->m = $m;
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->m->id,
            'message' => $this->m->merchant_order_id,
            'time' => $this->m->updated_at->toDateTimeString(),
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

    public function broadcastType()
    {
        return 'withdrawal';
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

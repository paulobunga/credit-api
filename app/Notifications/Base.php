<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\OneSignal\OneSignalMessage;
use App\Channels\Android\OneSignalAndroid as Android;
use App\Channels\PusherBeams\PusherBeams;
use App\Channels\PusherBeams\PusherMessage;

abstract class Base extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public Model $model;

    protected string $icon;

    public function __construct(Model $m)
    {
        $this->model = $m;
    }

    /**
     * Get icon path
     *
     * @return string
     */
    protected function getIcon(): string
    {
        return asset('favicon.ico');
    }

    /**
     * Get notification link
     *
     * @return string
     */
    protected function getLink(): string
    {
        return asset('favicon.ico');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return \App\Channels\PusherBeams\PusherMessage
     */
    public function via($notifiable)
    {
        return [
            'database',
            'broadcast',
            PusherBeams::class,
            Android::class,
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
        return array_merge(
            [
                'link' => $this->getLink(),
                'icon' => $this->getIcon(),
            ],
            $this->getData($notifiable)
        );
    }

    /**
     * Get data of message.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    protected function getData($notifiable)
    {
        return [
            'title' => 'hello',
            'body' => 'world'
        ];
    }

    /**
     * Notification message for IOS, Android and Web
     *
     * @param  mixed $notifiable
     * @return \App\Channels\PusherBeams\PusherMessage $msg
     */
    public function toPushNotification($notifiable)
    {
        $data = $this->toArray($notifiable);

        return PusherMessage::create()
            ->web()
            ->badge(1)
            ->icon($data['icon'])
            ->link($data['link'])
            ->title($data['title'])
            ->body($data['body']);
    }

    /**
     * Create onesignal message of Android
     * @param  mixed $notifiable
     * @return \NotificationChannels\OneSignal\OneSignalMessage
     */
    public function toAndroid($notifiable)
    {
        $data = $this->toArray($notifiable);
        return OneSignalMessage::create()
            ->setSubject($data['title'])
            ->setBody($data['body'])
            ->setData('url', str_replace('https://', 'gamepts://', $data['link']));
    }

    /**
     * Websocket message
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onQueue('pusher');
    }
}

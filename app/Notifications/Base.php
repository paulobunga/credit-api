<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\OneSignal\OneSignalMessage;
use App\Channels\OneSignal;

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
     * Get notify id
     *
     * @return string
     */
    protected function getNotifyId(): string
    {
      return $this->id;
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
            OneSignal::class
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
     * Create onesignal message of Web
     * @param  mixed $notifiable
     * @return \NotificationChannels\OneSignal\OneSignalMessage
     */
    public function toWeb($notifiable)
    {
        $data = $this->toArray($notifiable);

        return OneSignalMessage::create()
            ->setSubject($data['title'])
            ->setBody($data['body'])
            ->setUrl($data['link']);
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

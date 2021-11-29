<?php

namespace App\Channels\Web;

use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\Exceptions\CouldNotSendNotification;
use NotificationChannels\OneSignal\OneSignalChannel;

class OneSignalWeb extends OneSignalChannel
{
    public function __construct()
    {
        $client = new OneSignalClient(
            env("ONESIGNAL_APP_ID"),
            env("ONESIGNAL_REST_API_KEY"),
            ''
        );
        parent::__construct($client);
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \NotificationChannels\OneSignal\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$userIds = $notifiable->devices()->where('platform', 'web')->pluck('uuid')->toArray()) {
            return;
        }
        /** @var ResponseInterface $response */
        $response = $this->oneSignal->sendNotificationCustom(
            $this->payload($notifiable, $notification, $userIds)
        );

        if ($response->getStatusCode() !== 200) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
        }

        return $response;
    }

    /**
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @param mixed $targeting
     *
     * @return array
     */
    protected function payload($notifiable, $notification, $targeting)
    {
        $payload = $notification->toWeb($notifiable)->toArray();

        if ($this->isTargetingEmail($targeting)) {
            $payload['filters'] = collect([['field' => 'email', 'value' => $targeting['email']]]);
        } elseif ($this->isTargetingTags($targeting)) {
            $array = $targeting['tags'];
            $res = count($array) == count($array, COUNT_RECURSIVE);
            if ($res) {
                $payload['tags'] = collect([$targeting['tags']]);
            } else {
                $payload['tags'] = collect($targeting['tags']);
            }
        } elseif ($this->isTargetingIncludedSegments($targeting)) {
            $payload['included_segments'] = collect($targeting['included_segments']);
        } elseif ($this->isTargetingExcludedSegments($targeting)) {
            $payload['excluded_segments'] = collect($targeting['excluded_segments']);
        } elseif ($this->isTargetingExternalUserIds($targeting)) {
            $payload['include_external_user_ids'] = collect($targeting['include_external_user_ids']);
        } else {
            $payload['include_player_ids'] = collect($targeting);
        }

        return $payload;
    }

    /**
     * @param mixed $targeting
     *
     * @return bool
     */
    protected function isTargetingIncludedSegments($targeting)
    {
        return is_array($targeting) && array_key_exists('included_segments', $targeting);
    }

    /**
     * @param mixed $targeting
     *
     * @return bool
     */
    protected function isTargetingExternalUserIds($targeting)
    {
        return is_array($targeting) && array_key_exists('include_external_user_ids', $targeting);
    }

    /**
     * @param mixed $targeting
     *
     * @return bool
     */
    protected function isTargetingExcludedSegments($targeting)
    {
        return is_array($targeting) && array_key_exists('excluded_segments', $targeting);
    }

    /**
     * @param mixed $targeting
     *
     * @return bool
     */
    protected function isTargetingEmail($targeting)
    {
        return is_array($targeting) && array_key_exists('email', $targeting);
    }

    /**
     * @param mixed $targeting
     *
     * @return bool
     */
    protected function isTargetingTags($targeting)
    {
        return is_array($targeting) && array_key_exists('tags', $targeting);
    }
}

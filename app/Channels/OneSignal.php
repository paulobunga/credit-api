<?php

namespace App\Channels;

use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\Exceptions\CouldNotSendNotification;

class OneSignal
{
    protected $reseller_client;
    protected $merchant_client;

    public function __construct()
    {
        $this->merchant_client = new OneSignalClient(
            env("ONESIGNAL_MERCHANT_APP_ID"),
            env("ONESIGNAL_MERCHANT_REST_API_KEY"),
            ''
        );
        $this->reseller_client = new OneSignalClient(
            env("ONESIGNAL_RESELLER_APP_ID"),
            env("ONESIGNAL_RESELLER_REST_API_KEY"),
            ''
        );
    }

    protected function getOneSignalClient($name)
    {
        switch ($name) {
            case 'reseller':
                return $this->reseller_client;
            case 'merchant':
                return $this->merchant_client;
            default:
                return null;
        }
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
        $devices = $notifiable->devices()->get()->groupBy('platform');
        if ($devices->isEmpty()) {
            return;
        }
        $client = $this->getOneSignalClient($notifiable->getMorphClass());
        if (!$client) {
            return;
        }
        foreach ($devices as $platform => $dvs) {
            $response = $client->sendNotificationCustom(
                $this->payload(
                    $notifiable,
                    $notification,
                    [
                        'platform' => $platform,
                        'include_player_ids' => $dvs->pluck('uuid')->toArray()
                    ]
                )
            );

            if ($response->getStatusCode() !== 200) {
                Log::error($response->getBody());
                throw CouldNotSendNotification::serviceRespondedWithAnError($response);
            } else {
                Log::info($response->getBody());
            }
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
        $method = 'to' . ucfirst($targeting['platform']);
        $payload = $notification->$method($notifiable)->toArray();

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
            $payload['include_player_ids'] = collect($targeting['include_player_ids']);
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

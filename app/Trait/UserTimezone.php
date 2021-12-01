<?php

namespace App\Trait;

use Carbon\Carbon;

trait UserTimezone
{

    protected function convertToUserTimezone(?string $dateTime)
    {
        if (empty($dateTime)) {
            return $dateTime;
        }
        $user_timezone = auth()->user()->timezone ?? env('APP_TIMEZONE');

        return Carbon::parse($dateTime)->timezone($user_timezone);
    }

    public function getCreatedAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getNotifiedAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getExpiredAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getActivatedAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getSentAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getReceivedAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getStartAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }

    public function getEndAtAttribute($value)
    {
        return $this->convertToUserTimezone($value);
    }
}

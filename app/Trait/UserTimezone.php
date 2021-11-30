<?php

namespace App\Trait;

use Carbon\Carbon;

trait UserTimezone
{
    public function convertTimezone(?string $dateTime)
    {
        if(!empty($dateTime) || !is_null($dateTime)){
          $timezone = optional(auth()->user())->timezone ?? env('APP_TIMEZONE');
          return Carbon::parse($dateTime)->timezone($timezone);
        }
        return "";
    }
}

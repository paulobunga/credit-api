<?php

namespace App\Filters;

use Carbon\Carbon;

class BaseTimezone 
{
    public $db_timezone;
    
    public $user_timezone_name;

    public $user_timezone_offset;

    public function __construct() {
      $this->db_timezone = env('DB_TIMEZONE');
      $this->user_timezone_name = optional(auth()->user())->timezone ?? env('APP_TIMEZONE');
      $this->user_timezone_offset = $this->timezoneToOffset($this->user_timezone_name);
    }

    public function timezoneToOffset($timezone = 'UTC')
    {
      $time = new \DateTime('now', new \DateTimeZone($timezone));
      return $time->format('P');
    }

    public function convertToUserTimezone(?string $dateTime)
    {
      if(!empty($dateTime) || !is_null($dateTime)){
        return Carbon::parse($dateTime)->timezone($this->user_timezone_name);
      }
      return "";
    }
}
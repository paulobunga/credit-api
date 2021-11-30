<?php

namespace App\Trait;

use Carbon\Carbon;

trait UserTimezone
{
    public function userTimezoneName(){
      return optional(auth()->user())->timezone ?? env('APP_TIMEZONE');
    }

    public function convertToUserTimezone(?string $dateTime)
    {
      if(!empty($dateTime) || !is_null($dateTime)){
        return Carbon::parse($dateTime)->timezone($this->userTimezoneName());
      }
      return "";
    }

    public function timezoneToOffset($timezone = 'UTC'){
      $time = new \DateTime('now', new \DateTimeZone($timezone));
      return $time->format('P');
    }

    public function userTimezoneOffset(){
      return $this->timezoneToOffset($this->userTimezoneName());
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
}

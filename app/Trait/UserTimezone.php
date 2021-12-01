<?php

namespace App\Trait;

use App\Filters\BaseTimezone;

trait UserTimezone
{
    protected $base_timezone;

    public function __construct() {
      parent::__construct();
      $this->base_timezone = new BaseTimezone();
    }
    
    public function getCreatedAtAttribute($value) 
    {    
      return $this->base_timezone->convertToUserTimezone($value);
    }

    public function getUpdatedAtAttribute($value) 
    { 
      return $this->base_timezone->convertToUserTimezone($value);
    }

    public function getNotifiedAtAttribute($value) 
    { 
      return $this->base_timezone->convertToUserTimezone($value);
    }

    public function getExpiredAtAttribute($value) 
    {    
      return $this->base_timezone->convertToUserTimezone($value);
    }

    public function getActivatedAtAttribute($value) 
    { 
      return $this->base_timezone->convertToUserTimezone($value);
    }

    public function getSentAtAttribute($value) 
    {    
      return $this->base_timezone->convertToUserTimezone($value);
    }

    public function getReceivedAtAttribute($value) 
    {    
      return $this->base_timezone->convertToUserTimezone($value);
    }
}

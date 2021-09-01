<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

trait MerchantObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $query->uuid = $query->uuid ?? Str::uuid();
            $query->api_key = Str::random(30);
        });
    }
}

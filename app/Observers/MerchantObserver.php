<?php

namespace App\Observers;

use Illuminate\Support\Str;

trait MerchantObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $query->uuid = $query->uuid ?? Str::uuid();
            $query->api_key = $query->api_key ?? Str::random(30);
        });
    }
}

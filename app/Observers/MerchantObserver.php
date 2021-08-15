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
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM merchants")[0]->ID ?? 0;
            $query->uuid = $query->uuid ?? Str::random(4) . ($last_insert_id + 1) . '@' . Str::random(10);
            $query->api_key = Str::random(30);
        });
    }
}

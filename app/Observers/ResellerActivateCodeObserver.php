<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

trait ResellerActivateCodeObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM reseller_activate_codes")[0]->ID ?? 0;
            $query->code = Str::random(4) . ($last_insert_id + 1) . '@' . Str::random(20);
        });
    }
}

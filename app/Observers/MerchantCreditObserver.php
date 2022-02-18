<?php

namespace App\Observers;

use App\Models\Team;
use Illuminate\Support\Str;

trait MerchantCreditObserver
{
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // Assign to Default Team for all the merchant currency that is applicable.
            $teams = Team::where("currency", $model->currency)
                        ->where("name", "Default")
                        ->get();

            foreach ($teams as $team) {
                $model->merchant->assignTeams($team->id);
            }
        });
    }
}

<?php

namespace App\Observers;

use App\Models\Team;

trait ResellerObserver
{
    protected static function boot()
    {
        parent::boot();
        // auto-sets values on creation
        static::creating(function ($query) {
            $query->timezone = [
                'BDT' => 'Asia/Dhaka',
                'INR' => 'Asia/Kolkata',
                'VND' => 'Asia/Ho_Chi_Minh',
            ][strtoupper($query->currency)] ?? env('APP_TIMEZONE');
        });

        static::created(function ($model) {
            // auto-add online record after creation
            $model->online()->create([
                'status' => 0
            ]);

            // assign reseller to default team.
            $teams = Team::where("currency", $model->currency)
                        ->where("name", "Default")
                        ->get();

            foreach ($teams as $team) {
                $model->assignTeams($team->id);
            }
        });
    }
}

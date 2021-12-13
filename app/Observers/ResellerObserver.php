<?php

namespace App\Observers;

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

        // auto-add online record after creation
        static::created(function ($model) {
            $model->online()->create([
                'status' => 0
            ]);
        });
    }
}

<?php

namespace App\Observers;

trait ResellerObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-add online record after creation
        static::created(function ($model) {
            $model->online()->create([
                'status' => 0
            ]);
        });
    }
}

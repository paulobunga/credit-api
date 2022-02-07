<?php

namespace App\Trait;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait UserLogsActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $log = LogOptions::defaults();
        if (auth()->guard('admin')->check()) {
            return $log->logAll()
                       ->logExcept(['password', 'username'])
                       ->logOnlyDirty();
        } else {
            return $log->dontSubmitEmptyLogs();
        }
    }
}

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
            $route_name = request()->route()[1]['as'];
            return $log->logAll()
                    ->logOnlyDirty()
                    ->logExcept(['updated_at'])
                    ->useLogName($route_name)
                    ->setDescriptionForEvent(function (string $eventName) use ($route_name) {
                        $route_arr = explode(".", $route_name);
                        switch ($route_arr[2]) {
                            case 'update':
                            case 'store':
                            case 'destroy':
                                return $eventName . " " . str_replace("_", " ", $route_arr[1]);
                            default:
                                return str_replace("_", " ", $route_arr[2]) . " " . $route_arr[1];
                        }
                    });
        }
        return $log->dontSubmitEmptyLogs();
    }
}

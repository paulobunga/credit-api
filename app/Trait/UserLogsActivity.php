<?php

namespace App\Trait;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;

trait UserLogsActivity
{
    use LogsActivity;

    public function tapActivity(Activity $activity, string $eventName)
    {
        $route_name = request()->route()[1]['as'];
        $route_arr = explode(".", $route_name);
        $description = "";

        switch ($route_arr[2]) {
            case 'update':
            case 'store':
            case 'destroy':
                $description = $eventName . " " . str_replace("_", " ", $route_arr[1]);
                break;
            default:
                 $description = str_replace("_", " ", $route_arr[2]) . " " . $route_arr[1];
                break;
        }

        if ($eventName === "updated") {
            $news = [];
            $olds = [];
            foreach ($activity->properties["attributes"] as $key => $value) {
                if (is_array($value)) {
                    $news[$key] = array_udiff_assoc($activity->properties["attributes"][$key], $activity->properties["old"][$key], function ($new, $old) {
                        if ($old === null || $new === null) {
                            return 0;
                        }
                        return $new <=> $old;
                    });
                    $olds[$key] = collect($activity->properties["old"][$key])->only(array_keys($news[$key]))->all();
                } elseif ($key == "password") {
                    $news[$key] = "";
                    $olds[$key] = "";
                } else {
                    $news[$key] = $activity->properties["attributes"][$key];
                    $olds[$key] = $activity->properties["old"][$key];
                }
            }
            $activity->properties = [
              "attributes" => $news,
              "old" => $olds,
            ];
        }

        $activity->log_name = $route_name;
        $activity->description = $description;
    }

    public function getActivitylogOptions(): LogOptions
    {
        $log = LogOptions::defaults();
        if (auth()->guard('admin')->check()) {
            return $log->logFillable()->logOnlyDirty();
        }
        return $log->dontSubmitEmptyLogs();
    }
}

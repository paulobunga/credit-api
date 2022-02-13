<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as Model;
use App\Trait\UserTimezone;

class ActivityLog extends Model
{
    use UserTimezone;

    protected $casts = [
      'properties' => 'array',
      'created_at'  => 'datetime:Y-m-d H:i:s',
      'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserTimezone;

class ActivityLog extends Model
{
    use UserTimezone;

    protected $table = 'activity_log';

    protected $casts = [
      'properties' => 'array',
      'created_at'  => 'datetime:Y-m-d H:i:s',
      'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public function causer()
    {
        return $this->morphTo();
    }
}

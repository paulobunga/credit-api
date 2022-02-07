<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserLogsActivity;

class Notification extends Model
{
    use UserLogsActivity;

    protected $casts = [
      'id'  => 'string',
      'read_at'  => 'datetime:Y-m-d H:i:s',
      'created_at'  => 'datetime:Y-m-d H:i:s',
      'updated_at'  => 'datetime:Y-m-d H:i:s'
    ];
}

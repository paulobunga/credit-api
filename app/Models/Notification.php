<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $casts = [
      'id'  => 'string',
      'read_at'  => 'datetime:Y-m-d H:i:s',
      'created_at'  => 'datetime:Y-m-d H:i:s',
      'updated_at'  => 'datetime:Y-m-d H:i:s'
    ];
}

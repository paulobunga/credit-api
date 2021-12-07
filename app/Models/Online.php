<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserTimezone;

class Online extends Model
{
    use UserTimezone;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_type',
        'status',
        'last_seen_at'
    ];

    protected $casts = [
        'last_seen_at'  => 'datetime:Y-m-d H:i:s'
    ];
}

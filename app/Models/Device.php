<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_type',
        'platform',
        'uuid',
        'token',
        'logined_at',
    ];

    /**
     * Get the user that owns the device.
     */
    public function user()
    {
        return $this->morphTo();
    }
}

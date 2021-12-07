<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserTimezone;

class ResellerOnline  extends Model
{
    use UserTimezone;

    public $timestamps = false;

    protected $fillable = [
        'reseller_id',
        'status',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at'  => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}

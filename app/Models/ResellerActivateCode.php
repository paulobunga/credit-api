<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ResellerActivateCodeObserver;
use App\Trait\UserTimezone;

class ResellerActivateCode extends Model
{
    use ResellerActivateCodeObserver;
    use UserTimezone;

    public $timestamps = false;

    protected $fillable = [
        'reseller_id',
        'active_reseller_id',
        'code',
        'status',
        'expired_at',
        'activated_at',
    ];

    public const STATUS = [
        'PENDING' => 0,
        'ACTIVATED' => 1,
        'EXPIRED' => 2,
    ];

    protected $casts = [
        'expired_at'  => 'datetime:Y-m-d H:i:s',
        'activated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function activeReseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function getextractIdAttribute()
    {
        return substr(explode('@', $this->attributes['code'])[0], 4);
    }
}

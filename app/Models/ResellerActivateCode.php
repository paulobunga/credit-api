<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ResellerActivateCode extends Model
{
    use HasFactory;

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

    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $query->code = Str::random(4) .
                DB::select("SHOW TABLE STATUS LIKE 'reseller_activate_codes'")[0]->Auto_increment
                . '@'
                . Str::random(20);
        });
    }

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

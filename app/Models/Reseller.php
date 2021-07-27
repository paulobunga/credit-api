<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Trait\HasJWTSubject;
use Illuminate\Notifications\Notifiable;

class Reseller extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, HasJWTSubject;
    use Notifiable;

    protected $fillable = [
        'level',
        'name',
        'username',
        'phone',
        'password',
        'credit',
        'coin',
        'commission_percentage',
        'pending_limit',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
    ];

    protected const LEVELS = [
        'referrer' => 0,
        'master agent' => 1,
        'agent' => 2,
        'reseller' => 3
    ];

    public function bankCards()
    {
        return $this->hasMany(ResellerBankCard::class);
    }

    public static function getLevelID($level)
    {
        return self::LEVELS[$level] ?? null;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }
}

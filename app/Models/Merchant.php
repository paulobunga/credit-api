<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Observers\MerchantObserver;
use App\Trait\HasJWTSubject;

class Merchant extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, HasJWTSubject;
    use Notifiable;
    use MerchantObserver;

    protected $fillable = [
        'uuid',
        'name',
        'username',
        'phone',
        'api_key',
        'password',
        'callback_url',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function whiteList()
    {
        return $this->hasOne(MerchantWhiteList::class);
    }

    public function credits()
    {
        return $this->hasMany(MerchantCredit::class);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }
}

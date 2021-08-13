<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Trait\HasJWTSubject;
use Illuminate\Notifications\Notifiable;

class Merchant extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, HasJWTSubject;
    use Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'username',
        'phone',
        'password',
        'callback_url',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->uuid = $attributes['uuid'] ?? Str::uuid();
        $this->api_key = Str::random(30);
    }

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

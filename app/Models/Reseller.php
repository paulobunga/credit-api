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
        'downline_slot',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [];

    public const LEVEL = [
        'referrer' => 0,
        'master agent' => 1,
        'agent' => 2,
        'reseller' => 3
    ];

    public const STATUS = [
        'inactive' => 0,
        'active' => 1,
        'disabled' => 2,
    ];

    public function bankCards()
    {
        return $this->hasMany(ResellerBankCard::class);
    }

    public function agent()
    {
        return $this->belongsTo(Reseller::class, 'upline_id', 'id');
    }

    public function getMasterAgent()
    {
        return $this->agent->agent ?? null;
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }
}

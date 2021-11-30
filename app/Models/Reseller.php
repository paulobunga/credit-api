<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Trait\HasJWTSubject;
use App\DTO\ResellerPayIn;
use App\DTO\ResellerPayOut;
use App\Trait\UserTimezone;

class Reseller extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasJWTSubject;
    use Notifiable;
    use UserTimezone;

    public $pushNotificationType = 'users';

    protected $fillable = [
        'upline_id',
        'uplines',
        'level',
        'name',
        'username',
        'phone',
        'currency',
        'password',
        'credit',
        'coin',
        'payin',
        'payout',
        'downline_slot',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'uplines' => 'array',
        'payin' => ResellerPayIn::class,
        'payout' => ResellerPayOut::class,
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public const LEVEL = [
        'REFERRER' => 0,
        'AGENT_MASTER' => 1,
        'AGENT' => 2,
        'RESELLER' => 3
    ];

    public const STATUS = [
        'INACTIVE' => 0,
        'ACTIVE' => 1,
        'DISABLED' => 2,
    ];

    public function bankCards()
    {
        return $this->hasMany(ResellerBankCard::class);
    }

    public function agent()
    {
        return $this->belongsTo(Reseller::class, 'upline_id', 'id');
    }

    public function deposits()
    {
        return $this->hasMany(ResellerDeposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(ResellerWithdrawal::class);
    }

    public function devices()
    {
        return $this->morphMany(Device::class, 'user');
    }

    public function getWithdrawalCreditAttribute()
    {
        return $this->attributes['credit'] - $this->withdrawals()->where([
            'type' => ResellerWithdrawal::TYPE['CREDIT'],
            'status' => ResellerWithdrawal::STATUS['PENDING']
        ])->sum('amount');
    }

    public function getWithdrawalCoinAttribute()
    {
        return $this->attributes['coin'] - $this->withdrawals()->where([
            'type' => ResellerWithdrawal::TYPE['COIN'],
            'status' => ResellerWithdrawal::STATUS['PENDING']
        ])->sum('amount');
    }

    public function getDownlineAttribute()
    {
        return Reseller::whereRaw("JSON_CONTAINS(uplines, '{$this->id}')")->count();
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function getCreatedAtAttribute($value) 
    {    
      return $this->convertTimezone($value);
    }

    public function getUpdatedAtAttribute($value) 
    { 
      return $this->convertTimezone($value);
    }

}

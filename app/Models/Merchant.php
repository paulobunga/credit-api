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
use App\Trait\HasTeams;

class Merchant extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, HasJWTSubject;
    use Notifiable;
    use HasTeams;
    use MerchantObserver;

    public $pushNotificationType = 'users';

    protected $fillable = [
        'uuid',
        'name',
        'username',
        'phone',
        'api_key',
        'password',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public const STATUS = [
        'DISABLED' => false,
        'ACTIVE' => true,
    ];

    public function whiteList()
    {
        return $this->hasOne(MerchantWhiteList::class);
    }

    public function credits()
    {
        return $this->hasMany(MerchantCredit::class);
    }

    public function deposits()
    {
        return $this->hasMany(MerchantDeposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(MerchantWithdrawal::class);
    }

    public function devices()
    {
        return $this->morphMany(Device::class, 'user');
    }

    public function getCredit($currency)
    {
        return $this->credits()->where('currency', strtoupper($currency))->first()->credit ?? 0;
    }

    public function getWithdrawalCredit($currency)
    {
        return $this->getCredit($currency) -
            $this->withdrawals()->where([
                'status' => MerchantWithdrawal::STATUS['PENDING'],
                'currency' => strtoupper($currency)
            ])->sum('amount');
    }

    public function getTransactionFee($currency)
    {
        return $this->credits()->where('currency', strtoupper($currency))->first()->transaction_fee ?? 0;
    }

    public function getPayOutFee($currency, $amount)
    {
        return $this->getTransactionFee($currency) * $amount;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }
}

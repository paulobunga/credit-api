<?php

namespace App\Models;

use App\Trait\HasTeams;
use App\Trait\HasOnline;
use App\DTO\ResellerPayIn;
use App\DTO\ResellerPayOut;
use App\Trait\UserTimezone;
use App\Trait\HasJWTSubject;
use App\Observers\ResellerObserver;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Reseller extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable;
    use Authorizable;
    use HasJWTSubject;
    use Notifiable;
    use UserTimezone;
    use HasOnline;
    use HasTeams {
        assignTeams as _assignTeams;
        syncTeams as _syncTeams;
        removeTeam as _removeTeam;
    }
    use ResellerObserver;

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
        'timezone',
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
        'HOME' => 0,
        'SUPER_AGENT' => 1,
        'MASTER_AGENT' => 2,
        'AGENT' => 3
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

    public function assignTeams(...$teams)
    {
        if ($this->level != static::LEVEL['AGENT']) {
            return;
        }
        return $this->_assignTeams(...$teams);
    }

    public function syncTeams(...$teams)
    {
        if ($this->level != static::LEVEL['AGENT']) {
            return;
        }
        return $this->_syncTeams(...$teams);
    }

    public function removeTeam($team)
    {
        if ($this->level != static::LEVEL['AGENT']) {
            return;
        }
        return $this->_removeTeam($team);
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
}

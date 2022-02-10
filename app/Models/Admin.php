<?php

namespace App\Models;

use App\Trait\HasJWTSubject;
use App\Trait\UserLogsActivity;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

/**
 * Model of admin
 * @package Models
 */
class Admin extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, HasJWTSubject;
    use UserLogsActivity;
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'name',
        'username',
        'password',
        'status',
        'timezone'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public const STATUS = [
        'DISABLED' => false,
        'ACTIVE' => true,
    ];

    public function getIsSuperAdminAttribute()
    {
        return $this->hasRole('Super Admin');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function devices()
    {
        return $this->morphMany(Device::class, 'user');
    }

    public function activityLog()
    {
        return $this->morphMany(ActivityLog::class, 'causer');
    }
}

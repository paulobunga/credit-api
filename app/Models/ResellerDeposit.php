<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ResellerDepositObserver;
use App\Models\Transaction;
use App\DTO\ResellerDepositExtra;
use App\Trait\UserTimezone;
use App\Trait\HasNumFormat;

class ResellerDeposit extends Model
{
    use ResellerDepositObserver;
    use UserTimezone;
    use HasNumFormat;

    protected $fillable = [
        'reseller_id',
        'audit_admin_id',
        'order_id',
        'transaction_type',
        'type',
        'amount',
        'status',
        'extra',
    ];

    protected $casts = [
        'extra' => ResellerDepositExtra::class,
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public const TYPE = [
        'CREDIT' => 0,
        'COIN' => 1,
    ];

    public const STATUS = [
        'PENDING' => 0,
        'APPROVED' => 1,
        'REJECTED' => 2,
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function auditAdmin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }

    public function getExtraAttribute()
    {
        return json_decode($this->attributes['extra'], true);
    }

    public function getTypeTextAttribute()
    {
        return array_keys(self::TYPE)[$this->attributes['type']];
    }

    public function getStatusTextAttribute()
    {
        return array_keys(self::STATUS)[$this->attributes['status']];
    }
}

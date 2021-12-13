<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserTimezone;

class Transaction extends Model
{
    public $timestamps = false;
    use UserTimezone;

    protected $fillable = [
        'user_id',
        'user_type',
        'type',
        'amount',
        'before',
        'after',
        'currency',
    ];

    protected $casts = [
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public const TYPE = [
        'ADMIN_TOPUP_CREDIT' => 0,
        'ADMIN_WITHDRAW_CREDIT' => 1,
        'ADMIN_TOPUP_COIN' => 2,
        'ADMIN_WITHDRAW_COIN' => 3,
        'MERCHANT_TOPUP_CREDIT' => 4,
        'MERCHANT_WITHDRAW_CREDIT' => 5,
        'RESELLER_TOPUP_CREDIT' => 6,
        'RESELLER_WITHDRAW_CREDIT' => 7,
        'RESELLER_WITHDRAW_COIN' => 8,
        'SYSTEM_TRANSACTION_FEE' => 9,
        'SYSTEM_DEDUCT_CREDIT'=> 10,
        'SYSTEM_TOPUP_COMMISSION' => 11,
        'SYSTEM_TOPUP_CREDIT' => 12,
        'MERCHANT_SETTLE_CREDIT' => 13,
        'ROLLBACK_TRANSACTION_FEE' => 14,
    ];

    public function getTypeAttribute()
    {
        return array_search($this->attributes['type'], self::TYPE);
    }
}

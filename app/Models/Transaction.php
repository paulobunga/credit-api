<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_type',
        'type',
        'amount',
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
    ];

    public function getTypeAttribute()
    {
        return array_search($this->attributes['type'], self::TYPE);
    }
}

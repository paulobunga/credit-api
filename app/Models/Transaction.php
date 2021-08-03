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
        'DEDUCT_CREDIT' => 0,
        'TOPUP_CREDIT' => 1,
        'DEDUCT_COIN' => 2,
        'COMMISSION' => 3,
        'TRANSACTION_FEE' => 4,
    ];

    public function getTypeAttribute()
    {
        return array_search($this->attributes['type'], self::TYPE);
    }
}

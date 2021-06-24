<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'reseller_bank_card_id',
        'order_id',
        'merchant_order_id',
        'amount',
        'status',
        'callback_url',
        'reference_no'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function resellerBankCard()
    {
        return $this->hasOne(resellerBankCard::class, 'id', 'reseller_bank_card_id');
    }

    public function fundRecords()
    {
        return $this->morphMany('App\Models\MerchantFundRecord', 'fundable');
    }
}
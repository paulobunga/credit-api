<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Observers\MerchantDepositObserver;
use App\Models\Transaction;

class MerchantDeposit extends Model
{
    use MerchantDepositObserver;
    
    protected $fillable = [
        'merchant_id',
        'reseller_id',
        'reseller_bank_card_id',
        'order_id',
        'merchant_order_id',
        'account_no',
        'account_name',
        'amount',
        'status',
        'callback_status',
        'attempts',
        'callback_url',
        'reference_no',
        'info'
    ];

    public const STATUS = [
        'CREATED' => 0,
        'PENDING' => 1,
        'APPROVED' => 2,
        'REJECTED' => 3,
        'ENFORCED' => 4,
        'CANCELED' => 5,
    ];

    public const CALLBACK_STATUS = [
        'CREATED' => 0,
        'PENDING' => 1,
        'FINISH' => 2,
        'FAILED' => 3,
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function resellerBankCard()
    {
        return $this->hasOne(ResellerBankCard::class, 'id', 'reseller_bank_card_id');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function bank()
    {
        return $this->hasOneThrough(
            Bank::class,
            ResellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'bank_id'
        );
    }

    public function paymentMethod()
    {
        return $this->resellerBankCard->paymentMethod();
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }

    public function scopeCreatedAtBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('merchant_deposits.created_at', [$from, $to]);
    }
}

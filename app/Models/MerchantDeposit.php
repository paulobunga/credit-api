<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\MerchantDepositObserver;
use App\Models\Transaction;
use App\Trait\SignValidator;

class MerchantDeposit extends Model
{
    use MerchantDepositObserver;
    use SignValidator;

    protected $fillable = [
        'merchant_id',
        'reseller_bank_card_id',
        'order_id',
        'merchant_order_id',
        'method',
        'amount',
        'currency',
        'status',
        'callback_status',
        'attempts',
        'callback_url',
        'extra'
    ];

    protected $casts = [
        'extra' => 'array',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public const STATUS = [
        'CREATED' => 0,
        'PENDING' => 1,
        'APPROVED' => 2,
        'REJECTED' => 3,
        'ENFORCED' => 4,
        'CANCELED' => 5,
        'EXPIRED' => 6,
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

    public function reseller()
    {
        return $this->hasOneThrough(
            Reseller::class,
            ResellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'reseller_id'
        );
    }

    public function resellerBankCard()
    {
        return $this->hasOne(ResellerBankCard::class, 'id', 'reseller_bank_card_id');
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

    public function paymentChannel()
    {
        return $this->hasOneThrough(
            PaymentChannel::class,
            ResellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'payment_channel_id'
        );
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }

    public function getPayUrlAttribute()
    {
        $params = [
            'uuid' => $this->merchant->uuid,
            'merchant_order_id' => $this->merchant_order_id,
            'time' => $this->created_at->timestamp,
        ];

        return app('api.url')->version(env('API_VERSION'))
            ->route('api.deposits.pay', $params + [
                'sign' => $this->createSign($params, $this->merchant->api_key)
            ]);
    }

    public function getExpiredAtAttribute()
    {
        $min = app(\App\Settings\CurrencySetting::class)->getExpiredMinutes($this->attributes['currency']);
        return $this->created_at->addMinutes($min);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use App\Observers\MerchantWithdrawalObserver;
use App\Trait\SignValidator;
use App\Trait\UserTimezone;
use App\Trait\HasNumFormat;

class MerchantWithdrawal extends Model
{
    use MerchantWithdrawalObserver;
    use SignValidator;
    use UserTimezone;
    use HasNumFormat;

    protected $fillable = [
        'merchant_id',
        'reseller_id',
        'payment_channel_id',
        'order_id',
        'player_id',
        'merchant_order_id',
        'attributes',
        'amount',
        'currency',
        'status',
        'callback_status',
        'attempts',
        'callback_url',
        'extra',
        'notified_at'
    ];

    protected $casts = [
        'attributes' => 'array',
        'extra' => 'array',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
        'notified_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public const STATUS = [
        'CREATED' => 0,
        'PENDING' => 1,
        'FINISHED' => 2,
        'REJECTED' => 3,
        'APPROVED' => 4,
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

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function paymentChannel()
    {
        return $this->belongsTo(PaymentChannel::class);
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }

    public function getStatusTextAttribute()
    {
        return array_keys(self::STATUS)[$this->attributes['status']];
    }

    public function getPayUrlAttribute()
    {
        $params = [
            'uuid' => $this->merchant->uuid,
            'merchant_order_id' => $this->merchant_order_id,
            'time' => $this->created_at->timestamp,
        ];

        return app('api.url')->version(env('API_VERSION'))
            ->route('api.withdrawals.pay', $params + [
                'sign' => $this->createSign($params, $this->merchant->api_key)
            ]);
    }

    public function getExpiredAtAttribute()
    {
        $min = app(\App\Settings\CurrencySetting::class)->getExpiredMinutes($this->attributes['currency']);
        return $this->created_at->addMinutes($min);
    }

    public function getSlipUrlAttribute()
    {
        if (!in_array($this->attributes['status'], [
            self::STATUS['FINISHED'],
            self::STATUS['APPROVED'],
            self::STATUS['CANCELED'],
        ])) {
            return null;
        }
        return  Storage::disk('s3')->temporaryUrl(
            'withdrawals/' . $this->attributes['order_id'],
            Carbon::now()->addMinutes(1)
        );
    }
}

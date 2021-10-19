<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\DTO\PaymentChannelPayIn;
use App\DTO\PaymentChannelPayOut;
use App\Observers\PaymentChannelObserver;

class PaymentChannel extends Model
{
    use PaymentChannelObserver;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'banks',
        'currency',
        'attributes',
        'payin',
        'payout',
        'payment_methods',
    ];

    protected $casts = [
        'attributes' => 'array',
        'payin' => PaymentChannelPayIn::class,
        'payout' => PaymentChannelPayOut::class,
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public const METHOD = [
        'TEXT' => 0,
        'QRCODE' => 1,
    ];

    public const STATUS = [
        'INACTIVE' => 'false',
        'ACTIVE' => 'true',
    ];

    public function getBanksAttribute()
    {
        return Bank::whereIn('id', explode(',', $this->attributes['banks']))->get();
    }

    public function getPaymentMethodsAttribute()
    {
        return array_map(fn ($v) => array_search($v, self::METHOD), explode(',', $this->attributes['payment_methods']));
    }
}

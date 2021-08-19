<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'payment_methods',
        'status',
    ];

    public const METHOD = [
        'TEXT' => 0,
        'QRCODE' => 1,
    ];

    protected $casts = [
        'attributes' => 'array',
        'status' => 'boolean',
        'created_at'  => 'datetime:Y-m-d H:i:s',
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

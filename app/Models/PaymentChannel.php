<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentChannel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'banks',
        'currency',
        'payment_methods',
    ];

    public const METHOD = [
        'TRANSFER' => 0,
        'QRCODE' => 1,
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

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
        return explode(',', $this->attributes['banks']);
    }
}

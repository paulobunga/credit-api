<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerBankCard extends Model
{
    protected $fillable = [
        'reseller_id',
        'payment_channel_id',
        'attributes',
        'status',
    ];

    protected $casts = [
        'attributes' => 'array',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public const STATUS = [
        'INACTIVE' => 0,
        'ACTIVE' => 1,
        'DISABLED' => 2,
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function paymentChannel()
    {
        return $this->belongsTo(PaymentChannel::class);
    }

    public function merchantDeposits()
    {
        return $this->hasMany(MerchantDeposit::class);
    }
}

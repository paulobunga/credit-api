<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResellerBankCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'bank_id',
        'payment_channel_id',
        'account_no',
        'account_name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function merchantDeposits()
    {
        return $this->hasMany(MerchantDeposit::class);
    }

    public function paymentChannel()
    {
        return $this->belongsTo(PaymentChannel::class);
    }
}

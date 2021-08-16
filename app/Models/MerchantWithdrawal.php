<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\MerchantWithdrawalObserver;

class MerchantWithdrawal extends Model
{
    use MerchantWithdrawalObserver;

    protected $fillable = [
        'merchant_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'extra'
    ];

    public const STATUS = [
        'PENDING' => 0,
        'APPROVED' => 1,
        'REJECTED' => 2,
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }
}

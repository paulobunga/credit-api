<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'order_id',
        'amount',
        'status',
        'info'
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

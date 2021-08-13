<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantCredit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'merchant_id',
        'currency',
        'credit',
        'transaction_fee',
    ];

    protected $casts = [
        'transaction_fee' => 'float',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

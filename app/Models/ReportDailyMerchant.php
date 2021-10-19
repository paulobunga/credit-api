<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDailyMerchant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'merchant_id',
        'start_at',
        'end_at',
        'turnover',
        'credit',
        'transaction_fee',
        'currency',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];
    
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

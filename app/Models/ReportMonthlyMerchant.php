<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportMonthlyMerchant extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'merchant_id',
        'date',
        'turnover',
        'payin',
        'payout'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

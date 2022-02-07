<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Trait\UserLogsActivity;

class ReportMonthlyMerchant extends Model
{
    use HasFactory;
    use UserLogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'merchant_id',
        'date',
        'turnover',
        'payin',
        'payout',
        'currency'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

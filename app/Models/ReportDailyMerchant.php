<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserTimezone;
use App\Trait\UserLogsActivity;

class ReportDailyMerchant extends Model
{
    use UserTimezone;
    use UserLogsActivity;

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
        'start_at'  => 'datetime:Y-m-d H:i:s',
        'end_at'  => 'datetime:Y-m-d H:i:s',
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];
    
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

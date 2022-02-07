<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Trait\UserLogsActivity;

class ReportMonthlyReseller extends Model
{
    use HasFactory;
    use UserLogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'reseller_id',
        'date',
        'turnover',
        'payin',
        'payout',
        'coin'
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}

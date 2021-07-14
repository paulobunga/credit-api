<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportMonthlyReseller extends Model
{
    use HasFactory;

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

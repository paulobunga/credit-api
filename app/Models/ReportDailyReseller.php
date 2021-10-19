<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDailyReseller extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'reseller_id',
        'start_at',
        'end_at',
        'turnover',
        'credit',
        'coin',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}

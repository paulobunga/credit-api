<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserTimezone;

class ReportDailyReseller extends Model
{
    use UserTimezone;

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
        'start_at'  => 'datetime:Y-m-d H:i:s',
        'end_at'  => 'datetime:Y-m-d H:i:s',
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDailyMerchant extends Model
{
    public $timestamps = false;
    
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportResellerFund extends Model
{

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}

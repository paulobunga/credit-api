<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResellerWithdrawal extends Model
{
    use HasFactory;

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function fundRecords()
    {
        return $this->morphMany('App\Models\ResellerFundRecord', 'fundable');
    }
}
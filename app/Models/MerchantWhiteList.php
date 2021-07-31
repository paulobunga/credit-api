<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantWhiteList extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'ip',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

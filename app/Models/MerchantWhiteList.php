<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantWhiteList extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'merchant_id',
        'api',
        'backend',
    ];

    protected $casts = [
        'api' => 'array',
        'backend' => 'array',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

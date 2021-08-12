<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantWhiteList extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'merchant_id',
        'ip',
    ];

    protected $casts = [
        'ip' => 'array',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

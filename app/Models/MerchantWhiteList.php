<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserLogsActivity;

class MerchantWhiteList extends Model
{
    use UserLogsActivity;

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

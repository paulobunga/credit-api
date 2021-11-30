<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserTimezone;

class ResellerSms extends Model
{
    use UserTimezone;

    public $timestamps = false;

    protected $fillable = [
        'reseller_id',
        'model_id',
        'model_name',
        'platform',
        'address',
        'body',
        'status',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'sent_at'  => 'datetime:Y-m-d H:i:s',
        'received_at' => 'datetime:Y-m-d H:i:s',
    ];

    public const STATUS = [
        'PENDING' => 0,
        'MATCH' => 1,
        'UNMATCH' => 2,
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}

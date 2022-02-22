<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerCredit extends Model
{
    protected $fillable = [
        'reseller_id',
        'credit',
        'coin'
    ];

    protected $casts = [
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}

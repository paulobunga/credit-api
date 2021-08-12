<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'ident',
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function paymentChannels()
    {
        return $this->hasMany(PaymentChannel::class);
    }
}

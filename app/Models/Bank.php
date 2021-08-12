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
    ];

    public function paymentChannels()
    {
        return $this->hasMany(PaymentChannel::class);
    }
}

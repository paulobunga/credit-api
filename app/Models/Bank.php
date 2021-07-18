<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Trait\Filterable;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method_id',
        'ident',
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}

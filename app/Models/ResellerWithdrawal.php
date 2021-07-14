<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResellerWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'order_id',
        'amount',
        'status',
        'info'
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }
}

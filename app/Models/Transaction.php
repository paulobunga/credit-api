<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'transaction_method_id',
        'amount',
    ];

    public function merchantDeposits()
    {
        return $this->morphedByMany(MerchantDeposit::class, 'model', 'model_has_transactions');
    }

    public function resellerDeposits()
    {
        return $this->morphedByMany(ResellerDeposit::class, 'model', 'model_has_transactions');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ResellerWithdrawalObserver;
use App\Models\Transaction;

class ResellerWithdrawal extends Model
{
    use ResellerWithdrawalObserver;

    protected $fillable = [
        'reseller_id',
        'order_id',
        'amount',
        'status',
        'info'
    ];

    public const STATUS = [
        'PENDING' => 0,
        'APPROVED' => 1,
        'REJECTED' => 2,
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ResellerDepositObserver;
use App\Models\Transaction;

class ResellerDeposit extends Model
{
    use ResellerDepositObserver;

    protected $fillable = [
        'reseller_id',
        'audit_admin_id',
        'order_id',
        'transaction_type',
        'type',
        'amount',
        'status',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public const TYPE = [
        'CREDIT' => 0,
        'COIN' => 1,
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

    public function auditAdmin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }

    public function getExtraAttribute()
    {
        return json_decode($this->attributes['extra'], true);
    }
}

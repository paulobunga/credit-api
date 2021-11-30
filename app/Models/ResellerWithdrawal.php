<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ResellerWithdrawalObserver;
use App\Models\Transaction;
use App\DTO\ResellerWithdrawalExtra;
use App\Trait\UserTimezone;

class ResellerWithdrawal extends Model
{
    use ResellerWithdrawalObserver;
    use UserTimezone;

    protected $fillable = [
        'reseller_id',
        'reseller_bank_card_id',
        'audit_admin_id',
        'order_id',
        'transaction_type',
        'type',
        'amount',
        'status',
        'extra',
    ];

    protected $casts = [
        'extra' => ResellerWithdrawalExtra::class,
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
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

    public function bankCard()
    {
        return $this->belongsTo(ResellerBankCard::class, 'reseller_bank_card_id', 'id');
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
        return json_decode($this->attributes['extra'] ?? '', true);
    }

    public function getTypeTextAttribute()
    {
        return array_keys(self::TYPE)[$this->attributes['type']];
    }

    public function getStatusTextAttribute()
    {
        return array_keys(self::STATUS)[$this->attributes['status']];
    }
}

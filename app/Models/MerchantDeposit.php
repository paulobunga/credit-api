<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Trait\Sortable;
use App\Trait\Filterable;

class MerchantDeposit extends Model
{
    use HasFactory, Sortable, Filterable;

    protected $fillable = [
        'merchant_id',
        'reseller_bank_card_id',
        'order_id',
        'merchant_order_id',
        'amount',
        'status',
        'callback_url',
        'reference_no',
        'info'
    ];

    protected $filterable_fields = [
        'name' => 'like',
        'status' => '=',
    ];

    protected $sortable_fields = [
        'id' => 'id',
        'name' => 'name',
        'status' => 'status'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function resellerBankCard()
    {
        return $this->hasOne(resellerBankCard::class, 'id', 'reseller_bank_card_id');
    }

    public function bank()
    {
        return $this->hasOneThrough(
            Bank::class,
            resellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'bank_id'
        );
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }
}

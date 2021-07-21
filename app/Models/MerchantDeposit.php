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
        'account_no',
        'account_name',
        'amount',
        'status',
        'callback_status',
        'attempts',
        'callback_url',
        'reference_no',
        'info'
    ];

    protected $filterable_fields = [
        'name' => 'like',
        'status' => '=',
        'account_name' => 'like',
        'account_no' => 'like'
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
        return $this->hasOne(ResellerBankCard::class, 'id', 'reseller_bank_card_id');
    }

    public function reseller()
    {
        return $this->hasOneThrough(
            Reseller::class,
            ResellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'reseller_id'
        );
    }

    public function bank()
    {
        return $this->hasOneThrough(
            Bank::class,
            ResellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'bank_id'
        );
    }

    public function paymentMethod()
    {
        return $this->resellerBankCard->paymentMethod();
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }
}

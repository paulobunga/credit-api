<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantFundRecord extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'amount',
    ];

    public function fundable()
    {
        return $this->morphTo();
    }

    public function deposit()
    {
        if ($this->fundable_type !== 'App\Models\MerchantDeposit') {
            return null;
        }
        return $this->belongsTo(MerchantDeposit::class, 'fundable_id');
    }

    public function withdrawal()
    {
        if ($this->fundable_type !== 'App\Models\MerchantWithdrawal') {
            return null;
        }
        return $this->belongsTo(MerchantWithdrawal::class, 'fundable_id');
    }

    public function merchant()
    {
        switch ($this->fundable_type) {
            case 'App\Models\MerchantDeposit':
                return $this->hasOneThrough(
                    Merchant::class,
                    MerchantDeposit::class,
                    'merchant_id',
                    'id',
                    'fundable_id',
                    'id'
                );
            case 'App\Models\MerchantWithdrawal':
                return $this->hasOneThrough(
                    Merchant::class,
                    MerchantWithdrawal::class,
                    'merchant_id',
                    'id',
                    'fundable_id',
                    'id'
                );
        }
        return null;
    }
}

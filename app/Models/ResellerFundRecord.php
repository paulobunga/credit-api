<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResellerFundRecord extends Model
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
        if ($this->fundable_type !== 'App\Models\ResellerDeposit') {
            return null;
        }
        return $this->belongsTo(ResellerDeposit::class, 'fundable_id');
    }

    public function withdrawal()
    {
        if ($this->fundable_type !== 'App\Models\ResellerWithdrawal') {
            return null;
        }
        return $this->belongsTo(ResellerWithdrawal::class, 'fundable_id');
    }

    public function reseller()
    {
        switch ($this->fundable_type) {
            case 'App\Models\ResellerDeposit':
                return $this->hasOneThrough(
                    Reseller::class,
                    ResellerDeposit::class,
                    'reseller_id',
                    'id',
                    'fundable_id',
                    'id'
                );
            case 'App\Models\ResellerWithdrawal':
                return $this->hasOneThrough(
                    Reseller::class,
                    ResellerWithdrawal::class,
                    'reseller_id',
                    'id',
                    'fundable_id',
                    'id'
                );
        }
        return null;
    }
}

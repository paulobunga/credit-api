<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Trait\Sortable;
use App\Trait\Filterable;

class ResellerBankCard extends Model
{
    use HasFactory, Sortable, Filterable;

    protected $fillable = [
        'reseller_id',
        'bank_id',
        'type',
        'account_no',
        'account_name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $filterable_fields = [
        'status' => '=',
        'account_name' => 'like',
        'account_no' => 'like',
        'banks.name' => 'like',
        'banks.type' => '=',
    ];

    protected $sortable_fields = [
        'id' => 'id',
        'name' => 'name',
        'status' => 'status'
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function paymentMethod()
    {
        return $this->hasOneThrough(
            PaymentMethod::class,
            Bank::class,
            'id',
            'id',
            'bank_id',
            'payment_method_id'
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResellerBankCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'bank_id',
        'type',
        'account_no',
        'account_name',
        'status',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    protected $casts = [
        'status' => 'boolean',
    ];

}

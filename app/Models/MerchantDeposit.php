<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantDeposit extends Model
{
    use HasFactory;

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

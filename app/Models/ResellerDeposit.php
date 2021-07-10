<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResellerDeposit extends Model
{
    use HasFactory;

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }
}

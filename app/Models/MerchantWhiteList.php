<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Trait\Sortable;
use App\Trait\Filterable;

class MerchantWhiteList extends Model
{
    use HasFactory, Sortable, Filterable;

    protected $fillable = [
        'merchant_id',
        'ip',
    ];

    protected $filterable_fields = [
        'name' => 'like',
        'ip' => 'like',
    ];

    protected $sortable_fields = [
        'id' => 'id',
        'name' => 'name',
        'ip' => 'ip',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}

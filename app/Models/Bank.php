<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Trait\Sortable;

class Bank extends Model
{
    use HasFactory, Sortable;

    protected $fillable = [
        'ident',
        'name',
        'status'
    ];

    protected $sortable_fields = [
        'id' => 'id',
        'name' => 'name',
        'ident' => 'ident'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Trait\Sortable;
use App\Trait\Filterable;

class Bank extends Model
{
    use HasFactory, Sortable, Filterable;

    protected $fillable = [
        'ident',
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $filterable_fields = [
        'name' => 'like',
        'ident' => 'like',
        'status' => '=',
    ];

    protected $sortable_fields = [
        'id' => 'id',
        'name' => 'name',
        'ident' => 'ident',
        'status' => 'status'
    ];
}

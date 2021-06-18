<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminWhiteList extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'ip',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

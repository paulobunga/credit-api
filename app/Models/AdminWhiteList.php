<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Trait\Sortable;

class AdminWhiteList extends Model
{
    use HasFactory, Sortable;

    protected $fillable = [
        'admin_id',
        'ip'
    ];

    protected $sortable_fields = [
        'id' => 'id',
        'name' => 'admins.name',
        'ip' => 'ip'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

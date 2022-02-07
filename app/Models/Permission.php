<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as Model;
use App\Trait\UserLogsActivity;

class Permission extends Model
{
    use HasFactory;
    use UserLogsActivity;

    protected $appends = ['group'];

    public function getGroupAttribute()
    {
        return array_slice(explode('.', $this->name), 1, 1)[0];
    }
}

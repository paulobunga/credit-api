<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as Model;
use App\Trait\UserLogsActivity;

class Role extends Model
{
    use HasFactory;
    use UserLogsActivity;
}

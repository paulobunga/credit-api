<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Trait\UserLogsActivity;

class Team extends Model
{
    use UserLogsActivity;

    protected $fillable = [
        'name',
        'type',
        'currency',
        'description'
    ];

    protected $casts = [
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public const TYPE = [
        'PAYIN' => 'PAYIN',
        'PAYOUT' => 'PAYOUT',
    ];

    public function agents()
    {
        return $this->morphedByMany(Reseller::class, 'model', 'model_has_teams');
    }

    public function merchants()
    {
        return $this->morphedByMany(Merchant::class, 'model', 'model_has_teams');
    }
}

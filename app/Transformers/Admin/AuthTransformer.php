<?php

namespace App\Transformers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;
use App\Settings\CurrencySetting;

class AuthTransformer extends TransformerAbstract
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'username' => $m->username,
            'timezone' => $m->timezone,
            'status' => $m->status,
            'access_token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'role' => $m->roles[0]->name ?? null,
            'permissions' => $m->getAllPermissions()->pluck('name'),
            'currency' => app(CurrencySetting::class)->toArray(),
            'notifications' => $m->unreadNotifications->count()
        ];
    }
}

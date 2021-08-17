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

    public function transform(Model $admin)
    {
        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'username' => $admin->username,
            'status' => $admin->status,
            'access_token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'role' => $admin->roles[0]->name ?? null,
            'permissions' => $admin->getAllPermissions()->pluck('name'),
            'currency' => app(CurrencySetting::class)->toArray(),
        ];
    }
}

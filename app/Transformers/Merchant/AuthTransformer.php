<?php

namespace App\Transformers\Merchant;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Auth;

class AuthTransformer extends TransformerAbstract
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function transform(Model $merchant)
    {
        return [
            'id' => $merchant->id,
            'uuid' => $merchant->uuid,
            'name' => $merchant->name,
            'username' => $merchant->username,
            'phone' => $merchant->phone,
            'credits' => $merchant->credits,
            'api_key' => $merchant->api_key,
            'api_white_lists' => $merchant->whiteList->api ?? [],
            'backend_white_lists' => $merchant->whiteList->backend ?? [],
            'status' => $merchant->status,
            'access_token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ];
    }
}

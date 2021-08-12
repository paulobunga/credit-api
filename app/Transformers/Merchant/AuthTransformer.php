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
            'credit' => $merchant->credit,
            'transaction_fee' => $merchant->transaction_fee,
            'api_key' => $merchant->api_key,
            'api_white_lists' => $merchant->whiteList->ip ?? [],
            'callback_url' => $merchant->callback_url,
            'status' => $merchant->status,
            'access_token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ];
    }
}

<?php

namespace App\Transformers\Reseller;

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

    public function transform(Model $reseller)
    {
        return [
            'id' => $reseller->id,
            'name' => $reseller->name,
            'username' => $reseller->username,
            'phone' => $reseller->phone,
            'transaction_fee' => $reseller->transaction_fee,
            'credit' => $reseller->credit,
            'coin' => $reseller->coin,
            'pending_limit' => $reseller->pending_limit,
            'status' => $reseller->status,
            'access_token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ];
    }
}

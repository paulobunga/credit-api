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
            'level' => $reseller->level,
            'name' => $reseller->name,
            'username' => $reseller->username,
            'phone' => $reseller->phone,
            'currency' => $reseller->currency,
            'credit' => $reseller->credits->credit,
            'coin' => $reseller->credits->coin,
            'payin' => $reseller->payin,
            'payout' => $reseller->payout,
            'status' => $reseller->status,
            'access_token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'timezone' => $reseller->timezone,
        ];
    }
}

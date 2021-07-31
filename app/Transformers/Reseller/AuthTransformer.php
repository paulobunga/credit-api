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
            'credit' => $reseller->credit,
            'coin' => $reseller->coin,
            'pending_limit' => $reseller->pending_limit,
            'commission_percentage' => $reseller->commission_percentage,
            'status' => $reseller->status,
            'access_token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ];
    }
}

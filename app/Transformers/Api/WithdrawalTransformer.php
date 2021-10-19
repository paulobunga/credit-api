<?php

namespace App\Transformers\Api;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class WithdrawalTransformer extends TransformerAbstract
{
    protected array $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    /**
     * Transform response
     *
     * @param  \Illuminate\Database\Eloquent\Model $m
     * @return void
     */
    public function transform(Model $m)
    {
        return [
            'name' => $m->merchant->name,
            'order_id' => $m->order_id,
            'merchant_order_id' => $m->merchant_order_id,
            'player_id' => $m->player_id,
            'amount' => $m->amount,
            'currency' => $m->currency,
            'status' => $m->status,
            'callback_url' => $m->callback_url,
        ] + $this->params + $m->attributes;
    }
}

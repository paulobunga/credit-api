<?php

namespace App\Transformers\Merchant;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class DepositTransformer extends TransformerAbstract
{
    public function transform(Model $deposit)
    {
        return [
            'id' => $deposit->id,
            'name' => $deposit->merchant->name,
            'order_id' => $deposit->order_id,
            'merchant_order_id' => $deposit->merchant_order_id,
            'amount' => $deposit->amount,
            'status' => $deposit->status,
            'callback_url' => $deposit->callback_url,
            'reference_no' => $deposit->reference_no,
        ];
    }
}

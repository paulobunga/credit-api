<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerBankCardTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->reseller->name,
            'payment_channel_id' => $m->payment_channel_id,
            'attributes' => $m->attributes,
            'channel' => $m->paymentChannel->name,
            'primary' => $m->primary,
            'methods' => $m->paymentChannel->payment_methods,
            'currency' => $m->paymentChannel->currency,
            'status' => $m->status,
        ];
    }
}

<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class PaymentChannelTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'banks' => $m->banks,
            'payment_methods' => $m->payment_methods,
            'currency' => $m->currency,
            'status' => $m->status,
        ];
    }
}
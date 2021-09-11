<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class BankCardTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'channel' => $m->paymentChannel->name,
            'primary' => $m->primary,
            'attributes' => $m->attributes,
            'status' => $m->status,
        ];
    }
}

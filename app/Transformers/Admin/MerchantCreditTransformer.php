<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantCreditTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'credit' => $m->credit,
            'currency' => $m->currency,
            'transaction_fee' => $m->transaction_fee,
        ];
    }
}

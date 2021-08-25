<?php

namespace App\Transformers\Merchant;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'type' => $m->type,
            'amount' => $m->amount,
            'before' => $m->before,
            'after' => $m->after,
            'created_at' => $m->created_at,
        ];
    }
}

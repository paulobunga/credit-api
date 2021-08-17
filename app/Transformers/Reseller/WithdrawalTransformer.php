<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class WithdrawalTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'order_id' => $m->order_id,
            'amount' => $m->amount,
            'status' => $m->status,
            'extra' => $m->extra,
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection($m->transactions, new TransactionTransformer, false);
    }
}

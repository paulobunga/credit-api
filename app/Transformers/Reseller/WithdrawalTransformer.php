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
            'card' => $m->bankCard->primary ?? '--',
            'amount' => $m->amount,
            'status' => $m->status,
            'extra' => $m->extra,
            'created_at' => (string)$m->created_at
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection($m->transactions, new TransactionTransformer, false);
    }
}

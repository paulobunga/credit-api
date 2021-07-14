<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class WithdrawalTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $withdrawal)
    {
        return [
            'id' => $withdrawal->id,
            'order_id' => $withdrawal->order_id,
            'amount' => $withdrawal->amount,
            'status' => $withdrawal->status,
        ];
    }

    public function includeTransactions(Model $withdrawal)
    {
        return $this->collection($withdrawal->transactions, new TransactionTransformer, false);
    }
}

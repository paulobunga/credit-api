<?php

namespace App\Transformers\Merchant;

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
            'name' => $withdrawal->merchant->name,
            'order_id' => $withdrawal->order_id,
            'amount' => $withdrawal->amount,
            'status' => $withdrawal->status,
        ];
    }

    public function includeTransactions(Model $merchant_withdrawal)
    {
        return $this->collection($merchant_withdrawal->transactions, new TransactionTransformer, false);
    }
}

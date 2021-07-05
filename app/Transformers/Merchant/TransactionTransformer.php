<?php

namespace App\Transformers\Merchant;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    public function transform(Model $transaction)
    {
        return [
            'id' => $transaction->id,
            'name' => $transaction->transactionMethod->name,
            'amount' => $transaction->amount,
            'created_at' => $transaction->created_at,
        ];
    }
}
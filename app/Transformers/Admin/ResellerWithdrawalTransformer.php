<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerWithdrawalTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $reseller_withdrawal)
    {
        return [
            'id' => $reseller_withdrawal->id,
            'name' => $reseller_withdrawal->reseller->name,
            'order_id' => $reseller_withdrawal->order_id,
            'amount' => $reseller_withdrawal->amount,
            'currency' => $reseller_withdrawal->reseller->currency,
            'status' => $reseller_withdrawal->status,
            'created_at' => $reseller_withdrawal->created_at->toDateTimeString(),
        ];
    }

    public function includeTransactions(Model $withdrawal)
    {
        return $this->collection($withdrawal->transactions, new TransactionTransformer, false);
    }
}

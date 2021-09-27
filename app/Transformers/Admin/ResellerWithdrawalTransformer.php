<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerWithdrawalTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->reseller->name,
            'admin' => $m->auditAdmin->name ?? '--',
            'card' => $m->bankcard,
            'type' => $m->type,
            'transaction_type' => $m->transaction_type,
            'order_id' => $m->order_id,
            'amount' => $m->amount,
            'currency' => $m->reseller->currency,
            'status' => $m->status,
            'extra' => $m->extra,
            'created_at' => (string)$m->created_at,
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection($m->transactions, new TransactionTransformer, false);
    }
}

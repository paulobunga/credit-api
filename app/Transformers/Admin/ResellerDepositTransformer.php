<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerDepositTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'reseller' => $m->reseller->name,
            'admin' => $m->auditAdmin->name ?? '--',
            'order_id' => $m->order_id,
            'type' => $m->type,
            'transaction_type' => $m->transaction_type,
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

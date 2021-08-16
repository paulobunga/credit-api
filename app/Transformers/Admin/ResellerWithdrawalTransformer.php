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
            'order_id' => $m->order_id,
            'amount' => $m->amount,
            'currency' => $m->reseller->currency,
            'status' => $m->status,
            'reason' => $m->reason,
            'created_at' => $m->created_at->toDateTimeString(),
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection($m->transactions, new TransactionTransformer, false);
    }
}

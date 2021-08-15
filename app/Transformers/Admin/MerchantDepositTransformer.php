<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantDepositTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->merchant->name,
            'order_id' => $m->order_id,
            'merchant_order_id' => $m->merchant_order_id,
            'reseller_name' => $m->reseller->name,
            'amount' => $m->amount,
            'currency' => $m->currency,
            'status' => $m->status,
            'callback_url' => $m->callback_url,
            'reference_no' => $m->reference_no,
            'created_at' => $m->created_at->toDateTimeString(),
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection($m->transactions, new TransactionTransformer, false);
    }
}

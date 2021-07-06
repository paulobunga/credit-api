<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class DepositTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $deposit)
    {
        return [
            'id' => $deposit->id,
            'merchant_order_id' => $deposit->merchant_order_id,
            'account_name' => $deposit->account_name,
            'account_no' => $deposit->account_no,
            'amount' => $deposit->amount,
            'status' => $deposit->status,
            'callback_url' => $deposit->callback_url,
            'reference_no' => $deposit->reference_no,
        ];
    }

    public function includeTransactions(Model $deposit)
    {
        return $this->collection($deposit->transactions, new TransactionTransformer, false);
    }
}

<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantDepositTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $merchant_deposit)
    {
        return [
            'id' => $merchant_deposit->id,
            'name' => $merchant_deposit->merchant->name,
            'order_id' => $merchant_deposit->order_id,
            'merchant_order_id' => $merchant_deposit->merchant_order_id,
            'amount' => $merchant_deposit->amount,
            'status' => $merchant_deposit->status,
            'callback_url' => $merchant_deposit->callback_url,
            'reference_no' => $merchant_deposit->reference_no,
        ];
    }
    
    public function includeTransactions(Model $deposit)
    {
        return $this->collection($deposit->transactions, new TransactionTransformer, false);
    }
}

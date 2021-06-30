<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantWithdrawalTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $merchant_withdrawal)
    {
        return [
            'id' => $merchant_withdrawal->id,
            'name' => $merchant_withdrawal->merchant->name,
            'order_id' => $merchant_withdrawal->order_id,
            'amount' => $merchant_withdrawal->amount,
            'status' => $merchant_withdrawal->status,
        ];
    }

    public function includeTransactions(Model $merchant_withdrawal)
    {
        return $this->collection($merchant_withdrawal->transactions, new TransactionTransformer, false);
    }
}

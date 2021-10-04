<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantWithdrawalTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->merchant->name,
            'channel' => $m->paymentChannel->name,
            'order_id' => $m->order_id,
            'merchant_order_id' => $m->merchant_order_id,
            'reseller_name' => $m->reseller->name,
            'attributes' => $m->attributes,
            'amount' => $m->amount,
            'currency' => $m->currency,
            'status' => $m->status,
            'callback_url' => $m->callback_url,
            'attempts' => $m->attempts,
            'callback_status' => $m->callback_status,
            'created_at' => (string )$m->created_at,
            'updated_at' => (string) $m->updated_at,
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection($m->transactions, new TransactionTransformer, false);
    }
}

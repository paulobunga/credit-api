<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;
use App\Models\Transaction;

class DepositTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'order_id' => $m->order_id,
            'channel' => $m->paymentChannel->name,
            'card' => $m->resellerBankCard->primary,
            'method' => $m->method,
            'amount' => $m->amount,
            'status' => $m->status,
            'extra' => $m->extra,
            'created_at' => (string)$m->created_at
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection(
            $m->transactions()
                ->whereIn('transactions.type', [
                    Transaction::TYPE['SYSTEM_DEDUCT_CREDIT'],
                    Transaction::TYPE['SYSTEM_TOPUP_COMMISSION'],
                ])
                ->where('user_type', 'reseller')
                ->where('user_id', auth()->id())
                ->get(),
            new TransactionTransformer,
            false
        );
    }
}

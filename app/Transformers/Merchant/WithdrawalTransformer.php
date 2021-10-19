<?php

namespace App\Transformers\Merchant;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;
use App\Models\Transaction;

class WithdrawalTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'transactions',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'merchant_order_id' => $m->merchant_order_id,
            'channel' => $m->paymentChannel->name,
            'method' => $m->method,
            'amount' => $m->amount,
            'currency' => $m->currency,
            'status' => $m->status,
            'callback_url' => $m->callback_url,
            'callback_status' => $m->callback_status,
            'attempts' => $m->attempts,
            'extra' => $m->extra,
            'created_at' => (string)$m->created_at,
        ];
    }

    public function includeTransactions(Model $m)
    {
        return $this->collection(
            $m->transactions()
                ->whereIn('transactions.type', [
                    Transaction::TYPE['MERCHANT_WITHDRAW_CREDIT'],
                    Transaction::TYPE['SYSTEM_TRANSACTION_FEE'],
                ])
                ->where('user_type', 'merchant')
                ->where('user_id', auth()->id())
                ->get(),
            new TransactionTransformer,
            false
        );
    }
}

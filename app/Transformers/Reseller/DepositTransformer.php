<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
            'merchant_order_id' => $m->merchant_order_id,
            'channel' => $m->paymentChannel->name,
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
                ->where('user_id', Auth::id())
                ->get(),
            new TransactionTransformer,
            false
        );
    }
}

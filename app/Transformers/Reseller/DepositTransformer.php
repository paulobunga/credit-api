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
        return $this->collection(
            $deposit->transactions()
                ->whereIn('transactions.type', [
                    Transaction::TYPE['DEDUCT_CREDIT'],
                    Transaction::TYPE['COMMISSION'],
                ])
                ->where('user_type', 'reseller')
                ->where('user_id', Auth::id())
                ->get(),
            new TransactionTransformer,
            false
        );
    }
}

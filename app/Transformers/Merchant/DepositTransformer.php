<?php

namespace App\Transformers\Merchant;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
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
            'callback_status' => $deposit->callback_status,
            'attempts' => $deposit->attempts,
            'reference_no' => $deposit->reference_no,
        ];
    }

    public function includeTransactions(Model $deposit)
    {
        return $this->collection(
            $deposit->transactions()
                ->whereIn('transactions.type', [
                    Transaction::TYPE['MERCHANT_TOPUP_CREDIT'],
                    Transaction::TYPE['SYSTEM_TRANSACTION_FEE'],
                ])
                ->where('user_type', 'merchant')
                ->where('user_id', Auth::id())
                ->get(),
            new TransactionTransformer,
            false
        );
    }
}

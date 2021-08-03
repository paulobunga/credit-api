<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    public function transform(Model $transaction)
    {
        $user_class = '\\App\\Models\\' . ucwords($transaction->user_type);
        $user = $user_class::find($transaction->user_id);

        return [
            'id' => $transaction->id,
            'user' => [
                'level' => $user->level,
                'type' => $transaction->user_type,
                'name' => $user->name,
            ],
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'created_at' => $transaction->created_at,
        ];
    }
}

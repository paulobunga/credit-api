<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        $user_class = '\\App\\Models\\' . ucwords($m->user_type);
        $user = $user_class::find($m->user_id);

        return [
            'id' => $m->id,
            'user' => [
                'level' => $user->level,
                'type' => $m->user_type,
                'name' => $user->name,
            ],
            'type' => $m->type,
            'amount' => $m->amount,
            'before' => $m->before,
            'after' => $m->after,
            'created_at' => (string)$m->created_at,
        ];
    }
}

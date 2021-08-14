<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'level' => $m->level,
            'name' => $m->name,
            'username' => $m->username,
            'phone' => $m->phone,
            'credit' => $m->credit,
            'coin' => $m->coin,
            'currency' => $m->currency,
            'pending_limit' => $m->pending_limit,
            'commission_percentage' => $m->commission_percentage,
            'downline_slot' => $m->downline_slot,
            'status' => $m->status,
        ];
    }
}

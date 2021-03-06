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
            'credit' => $m->credits->credit,
            'coin' => $m->credits->coin,
            'currency' => $m->currency,
            'payin' => $m->payin,
            'payout' => $m->payout,
            'downline_slot' => $m->downline_slot,
            'status' => $m->status,
            'online_status' => $m->online->status,
            'last_seen_at' => (string)$m->online->last_seen_at,
            'timezone' => $m->timezone,
        ];
    }
}

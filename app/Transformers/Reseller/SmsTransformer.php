<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class SmsTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'address' => $m->address,
            'body' => $m->body,
            'status' => $m->status,
            'sent_at' => (string)$m->sent_at,
            'received_at' => (string)$m->received_at,
        ];
    }
}

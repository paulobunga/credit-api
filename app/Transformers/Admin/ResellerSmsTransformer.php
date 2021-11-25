<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerSmsTransformer extends TransformerAbstract
{

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->reseller->name,
            'address' => $m->address,
            'body' => $m->body,
            'status' => $m->status,
            'sent_at' => (string)$m->sent_at,
            'received_at' => (string)$m->received_at,
            'created_at' => (string)$m->created_at,
        ];
    }
}

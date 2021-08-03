<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ActivateCodeTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'reseller' => $m->activeReseller->name ?? '--',
            'code' => $m->code,
            'status' => $m->status,
            'expired_at' => $m->expired_at,
            'activated_at' => $m->activated_at,
        ];
    }
}

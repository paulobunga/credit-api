<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class LogTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'message' => $m->message,
            'channel' => $m->channel,
            'level' => $m->level_name,
            'context' => $m->context,
            'extra' => $m->extra,
            'created_at' => $m->created_at
        ];
    }
}

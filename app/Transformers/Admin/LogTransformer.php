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
            'channel' => $m->channel,
            'level' => $m->level,
            'message' => $m->message,
            'context' => json_encode($m->context, JSON_UNESCAPED_SLASHES),
            'created_at' => (string)$m->created_at
        ];
    }
}

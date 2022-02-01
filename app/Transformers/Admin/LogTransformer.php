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
            'context' => gettype($m->context) === 'string' ? $m->context : json_encode($m->context),
            'created_at' => (string)$m->created_at
        ];
    }
}

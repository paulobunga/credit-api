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
            'url' => $m->url,
            'message' => $m->message,
            'context' => $m->context,
            'created_at' => (string)$m->created_at
        ];
    }
}

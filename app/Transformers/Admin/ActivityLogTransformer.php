<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ActivityLogTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'log_name' => $m->log_name,
            'description' => $m->description,
            'event' => $m->event,
            'causer' => $m->admin,
            'properties' => $m->properties,
            'created_at' => (string)$m->created_at,
        ];
    }
}

<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class TeamTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'type' => $m->type,
            'currency' => $m->currency,
            'description' => $m->description,
            'agents' => $m->agents->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                ];
            }),
            'merchants' => $m->merchants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                ];
            }),
        ];
    }
}

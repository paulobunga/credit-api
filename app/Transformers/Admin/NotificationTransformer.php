<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{
  public function transform(Model $m)
  {
    return [
      'id' => $m->id,
      'notifiable_type' => $m->notifiable_type,
      'notifiable_id' => $m->notifiable_id,
      'data' => $m->data,
      'read_at' => (string)$m->read_at,
      'created_at' => (string)$m->created_at,
      'updated_at' => (string)$m->updated_at,
    ];
  }
}

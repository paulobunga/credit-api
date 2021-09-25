<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'group' => $m->group,
        ];
    }
}

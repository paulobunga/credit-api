<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract
{
    public function transform(Model $role)
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->getAllPermissions()->pluck('name')
        ];
    }
}

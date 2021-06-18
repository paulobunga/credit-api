<?php
namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class AdminWhiteListTransformer extends TransformerAbstract
{
    public function transform(Model $admin_white_list)
    {
        return [
            'id' => $admin_white_list->id,
            'admin_id' => $admin_white_list->admin->id,
            'name' => $admin_white_list->admin->name,
            'ip' => $admin_white_list->ip,
            'status' => $admin_white_list->status,
        ];
    }
}

<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class AdminWhiteListTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'admin'
    ];

    public function transform(Model $admin_white_list)
    {
        return [
            'id' => $admin_white_list->id,
            'ip' => $admin_white_list->ip,
        ];
    }

    public function includeAdmin(Model $admin_white_list)
    {
        return $this->item($admin_white_list->admin, new AdminTransformer, false);
    }
}

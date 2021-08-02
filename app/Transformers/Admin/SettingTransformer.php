<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class SettingTransformer extends TransformerAbstract
{
    public function transform(Model $setting)
    {
        return [
            'id' => $setting->id,
            'group' => $setting->group,
            'name' => $setting->name,
            'payload' => $setting->payload
        ];
    }
}

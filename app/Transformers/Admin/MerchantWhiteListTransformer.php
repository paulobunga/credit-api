<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantWhiteListTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'api' => $m->api,
            'backend' => $m->backend,
            'merchant_id' => $m->merchant_id,
        ];
    }
}

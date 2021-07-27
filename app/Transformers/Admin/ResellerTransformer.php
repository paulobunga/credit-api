<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerTransformer extends TransformerAbstract
{
    public function transform(Model $reseller)
    {
        return [
            'id' => $reseller->id,
            'level' => $reseller->level,
            'name' => $reseller->name,
            'username' => $reseller->username,
            'phone' => $reseller->phone,
            'credit' => $reseller->credit,
            'coin' => $reseller->coin,
            'commission_percentage' => $reseller->commission_percentage,
            'pending_limit' => $reseller->pending_limit,
            'status' => $reseller->status,
        ];
    }
}

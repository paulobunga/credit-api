<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantTransformer extends TransformerAbstract
{
    public function transform(Model $merchant)
    {
        return [
            'id' => $merchant->id,
            'uuid' => $merchant->uuid,
            'name' => $merchant->name,
            'username' => $merchant->username,
            'phone' => $merchant->phone,
            'api_key' => $merchant->api_key,
            'callback_url' => $merchant->callback_url,
            'status' => $merchant->status,
        ];
    }
}

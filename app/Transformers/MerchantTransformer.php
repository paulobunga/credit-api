<?php
namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantTransformer extends TransformerAbstract
{
    public function transform(Model $merchant)
    {
        return [
            'id' => $merchant->id,
            'name' => $merchant->name,
            'username' => $merchant->username,
            'merchant_id' => $merchant->merchant_id,
            'api_key' => $merchant->api_key,
            'api_whitelist' => $merchant->api_whitelist,
            'callback_url' => $merchant->callback_url,
            'status' => $merchant->status,
        ];
    }
}

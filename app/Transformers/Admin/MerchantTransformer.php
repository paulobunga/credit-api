<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'credits'
    ];

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

    public function includeCredits(Model $m)
    {
        return $this->collection($m->credits, new MerchantCreditTransformer, false);
    }
}

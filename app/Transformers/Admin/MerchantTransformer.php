<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'credits',
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'uuid' => $m->uuid,
            'name' => $m->name,
            'username' => $m->username,
            'phone' => $m->phone,
            'api_key' => $m->api_key,
            'whiteList' => $m->whiteList,
            'status' => $m->status,
        ];
    }

    public function includeCredits(Model $m)
    {
        return $this->collection($m->credits, new MerchantCreditTransformer, false);
    }
}

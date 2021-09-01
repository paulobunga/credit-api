<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantWhiteListTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'merchant'
    ];

    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'api' => $m->api,
            'backend' => $m->backend,
        ];
    }

    public function includeMerchant(Model $m)
    {
        return $this->item($m->merchant, new MerchantTransformer, false);
    }
}

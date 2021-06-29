<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantWhiteListTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'merchant'
    ];

    public function transform(Model $merchant_white_list)
    {
        return [
            'id' => $merchant_white_list->id,
            'ip' => $merchant_white_list->ip,
        ];
    }

    public function includeMerchant(Model $merchant_white_list)
    {
        return $this->item($merchant_white_list->merchant, new MerchantTransformer, false);
    }
}

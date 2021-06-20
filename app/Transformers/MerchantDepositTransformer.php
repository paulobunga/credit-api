<?php
namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantDepositTransformer extends TransformerAbstract
{
    public function transform(Model $merchant_deposit)
    {
        return [
            'id' => $merchant_deposit->id,
            'name' => $merchant_deposit->merchant->name,
            'order_id' => $merchant_deposit->order_id,
            'merchant_order_id' => $merchant_deposit->merchant_order_id,
            'amount' => $merchant_deposit->amount,
            'status' => $merchant_deposit->status,
            'callback_url' => $merchant_deposit->callback_url,
            'reference_no' => $merchant_deposit->reference_no,
        ];
    }
}

<?php
namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerWithdrawalTransformer extends TransformerAbstract
{
    public function transform(Model $reseller_withdrawal)
    {
        return [
            'id' => $reseller_withdrawal->id,
            'name' => $reseller_withdrawal->reseller->name,
            'order_id' => $reseller_withdrawal->order_id,
            'amount' => $reseller_withdrawal->amount,
            'status' => $reseller_withdrawal->status,
        ];
    }
}

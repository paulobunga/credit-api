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
            'name' => $reseller->name,
            'username' => $reseller->username,
            'phone' => $reseller->phone,
            'credit' => $reseller->credit,
            'coin' => $reseller->coin,
            'transaction_fee' => $reseller->transaction_fee,
            'pending_limit' => $reseller->pending_limit,
            'status' => $reseller->status,
        ];
    }
}

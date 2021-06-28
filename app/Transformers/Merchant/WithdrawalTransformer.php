<?php
namespace App\Transformers\Merchant;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class WithdrawalTransformer extends TransformerAbstract
{
    public function transform(Model $withdrawal)
    {
        return [
            'id' => $withdrawal->id,
            'name' => $withdrawal->merchant->name,
            'order_id' => $withdrawal->order_id,
            'amount' => $withdrawal->amount,
            'status' => $withdrawal->status,
        ];
    }
}

<?php

namespace App\Transformers\Api;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class DepositTransformer extends TransformerAbstract
{
    protected array $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function transform(Model $deposit)
    {
        return [
            'name' => $deposit->merchant->name,
            'order_id' => $deposit->order_id,
            'account_no' => $deposit->resellerBankCard->account_no,
            'account_name' => $deposit->resellerBankCard->account_name,
            'merchant_order_id' => $deposit->merchant_order_id,
            'amount' => $deposit->amount,
            'status' => $deposit->status,
            'callback_url' => $deposit->callback_url,
        ] + $this->params;
    }
}

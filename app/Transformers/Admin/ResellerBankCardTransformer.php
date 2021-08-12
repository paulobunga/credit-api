<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerBankCardTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'reseller_name' => $m->reseller->name,
            'bank_id' => $m->bank->id,
            'bank_name' => $m->bank->name,
            'payment_channel_id' => $m->payment_channel_id,
            'channel' => $m->paymentChannel->name,
            'account_no' => substr_replace($m->account_no, str_repeat('*', strlen($m->account_no) - 6), 0, -6),
            'account_name' => $m->account_name,
            'status' => $m->status,
        ];
    }
}

<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ReportMerchantTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'merchant' => $m->merchant->name,
            'start_at' => (string) $m->start_at,
            'end_at' => (string)$m->end_at,
            'turnover' => $m->turnover,
            'credit' => $m->credit,
            'transaction_fee' => $m->transaction_fee,
            'payin' => $m->extra['payin'],
            'payout' => $m->extra['payout'],
            'currency' => $m->currency
        ];
    }
}

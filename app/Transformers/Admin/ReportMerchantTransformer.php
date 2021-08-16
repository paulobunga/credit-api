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
            'start_at' => $m->start_at,
            'end_at' => $m->end_at,
            'turnover' => $m->turnover,
            'credit' => $m->credit,
            'transaction_fee' => $m->transaction_fee,
            'currency' => $m->currency
        ];
    }
}

<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ReportResellerTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'name' => $m->reseller->name,
            'start_at' => (string)$m->start_at,
            'end_at' => (string)$m->end_at,
            'turnover' => $m->turnover,
            'credit' => $m->credit,
            'coin' => $m->coin,
            'cashin' => $m->extra['cashin'],
            'cashout' => $m->extra['cashout'],
            'currency' => $m->reseller->currency,
        ];
    }
}

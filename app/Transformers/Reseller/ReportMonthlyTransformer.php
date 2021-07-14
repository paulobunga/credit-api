<?php

namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ReportMonthlyTransformer extends TransformerAbstract
{
    public function transform(Model $report)
    {
        return [
            'id' => $report->id,
            'date' => $report->date,
            'turnover' => $report->turnover,
            'payin' => $report->payin,
            'payout' => $report->payout,
            'coin' => $report->coin,
        ];
    }
}

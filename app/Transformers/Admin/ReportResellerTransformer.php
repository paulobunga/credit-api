<?php

namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ReportResellerTransformer extends TransformerAbstract
{
    public function transform(Model $report)
    {
        return [
            'id' => $report->id,
            'name' => $report->reseller->name,
            'start_at' => $report->start_at,
            'end_at' => $report->end_at,
            'turnover' => $report->turnover,
            'credit' => $report->credit,
            'coin' => $report->coin,
            'currency' => $report->reseller->currency,
        ];
    }
}

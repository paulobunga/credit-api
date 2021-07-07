<?php
namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ReportTransformer extends TransformerAbstract
{
    public function transform(Model $report)
    {
        return [
            'id' => $report->id,
            'reseller' => $report->reseller->name,
            'start_at' => $report->start_at,
            'end_at' => $report->end_at,
            'turnover' => $report->turnover,
            'credit' => $report->credit,
            'coin' => $report->coin,
        ];
    }
}

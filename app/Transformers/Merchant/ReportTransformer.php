<?php
namespace App\Transformers\Merchant;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ReportTransformer extends TransformerAbstract
{
    public function transform(Model $report)
    {
        return [
            'id' => $report->id,
            'merchant' => $report->merchant->name,
            'start_at' => $report->start_at,
            'end_at' => $report->end_at,
            'turnover' => $report->turnover,
            'credit' => $report->credit,
            'transaction_fee' => $report->transaction_fee,
        ];
    }
}

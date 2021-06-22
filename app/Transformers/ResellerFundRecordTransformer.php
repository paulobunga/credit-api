<?php
namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerFundRecordTransformer extends TransformerAbstract
{
    public function transform(Model $reseller_fund_record)
    {
        return [
            'id' => $reseller_fund_record->id,
            'reseller' => $reseller_fund_record->reseller->name,
            'type' => $reseller_fund_record->type,
            'amount' => $reseller_fund_record->amount,
        ];
    }
}

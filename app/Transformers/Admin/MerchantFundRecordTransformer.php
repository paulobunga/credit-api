<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class MerchantFundRecordTransformer extends TransformerAbstract
{
    public function transform(Model $reseller_fund_record)
    {
        return [
            'id' => $reseller_fund_record->id,
            'merchant' => $reseller_fund_record->merchant->name,
            'type' => $reseller_fund_record->type,
            'amount' => $reseller_fund_record->amount,
        ];
    }
}

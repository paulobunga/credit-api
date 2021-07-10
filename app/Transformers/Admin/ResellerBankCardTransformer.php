<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerBankCardTransformer extends TransformerAbstract
{
    public function transform(Model $reseller_bank_card)
    {
        return [
            'id' => $reseller_bank_card->id,
            'reseller' => $reseller_bank_card->reseller->name,
            'bank' => $reseller_bank_card->bank->name,
            'bank_id' => $reseller_bank_card->bank->id,
            'type' => $reseller_bank_card->paymentMethod->name,
            'account_no' => $reseller_bank_card->account_no,
            'account_name' => $reseller_bank_card->account_name,
            'status' => $reseller_bank_card->status,
        ];
    }
}

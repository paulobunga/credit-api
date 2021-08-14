<?php
namespace App\Transformers\Reseller;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class BankCardTransformer extends TransformerAbstract
{
    public function transform(Model $reseller_bank_card)
    {
        return [
            'id' => $reseller_bank_card->id,
            'bank' => $reseller_bank_card->bank->name,
            'bank_id' => $reseller_bank_card->bank->id,
            'ident' => $reseller_bank_card->bank->ident,
            'channel' => $reseller_bank_card->paymentChannel->name,
            'account_no' => $reseller_bank_card->account_no,
            'account_name' => $reseller_bank_card->account_name,
            'status' => $reseller_bank_card->status,
        ];
    }
}

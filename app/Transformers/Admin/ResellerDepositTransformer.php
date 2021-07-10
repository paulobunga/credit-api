<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class ResellerDepositTransformer extends TransformerAbstract
{
    public function transform(Model $reseller_deposit)
    {
        return [
            'id' => $reseller_deposit->id,
            'reseller' => $reseller_deposit->reseller->name,
            'admin' => $reseller_deposit->admin->name,
            'amount' => $reseller_deposit->amount,
            'status' => $reseller_deposit->status,
            'callback_url' => $reseller_deposit->callback_url,
            'reference_no' => $reseller_deposit->reference_no,
        ];
    }
}

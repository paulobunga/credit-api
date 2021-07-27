<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class BankTransformer extends TransformerAbstract
{
    public function transform(Model $bank)
    {
        return [
            'id' => $bank->id,
            'ident' => $bank->ident,
            'name' => $bank->name,
            'type' => $bank->paymentMethod->name,
            'status' => $bank->status,
        ];
    }
}

<?php
namespace App\Transformers;

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
            'status' => $bank->status,
        ];
    }
}

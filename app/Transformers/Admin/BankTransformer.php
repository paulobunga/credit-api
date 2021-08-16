<?php
namespace App\Transformers\Admin;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class BankTransformer extends TransformerAbstract
{
    public function transform(Model $m)
    {
        return [
            'id' => $m->id,
            'ident' => $m->ident,
            'name' => $m->name,
            'currency' => $m->currency,
            'status' => $m->status,
        ];
    }
}

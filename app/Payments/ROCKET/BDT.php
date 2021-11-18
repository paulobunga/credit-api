<?php

namespace App\Payments\ROCKET;

class BDT
{
    public $primary = 'wallet_number';

    public $attributes = [
        'wallet_number'
    ];

    public function rules()
    {
        return [
            'wallet_number' => 'required|regex:/^0\d{11}$/i',
        ];
    }
}

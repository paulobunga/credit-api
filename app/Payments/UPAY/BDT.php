<?php

namespace App\Payments\UPAY;

class BDT
{
    public $primary = 'wallet_number';

    public $attributes = [
        'wallet_number'
    ];

    public function rules()
    {
        return [
            'wallet_number' => 'required|regex:/^0\d{10}$/i',
        ];
    }
}

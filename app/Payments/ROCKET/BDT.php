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
            'wallet_number' => 'required|regex:/^01\d{10}$/i',
        ];
    }
}

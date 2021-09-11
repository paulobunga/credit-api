<?php

namespace App\Payments\MOMOPAY;

class VND
{
    public $primary = 'qrcode';

    public $attributes = [
        'qrcode'
    ];

    public function rules()
    {
        return [
            'qrcode' => 'required',
        ];
    }
}

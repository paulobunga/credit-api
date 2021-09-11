<?php

namespace App\Payments\ZALOPAY;

class VND
{
    public $primary = 'qrcode';

    public $attributes = [
        'qrcode'
    ];
    
    public function rule()
    {
        return  [
            'qrcode' => 'required',
        ];
    }
}

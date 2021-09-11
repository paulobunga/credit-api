<?php

namespace App\Payments\VIETTELPAY;

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

<?php

namespace App\Payments\NETBANK;

class INR
{
    public $primary = 'account_number';

    public $attributes = [
        'account_name',
        'account_number',
        'ifsc_code'
    ];

    public function rules()
    {
        return [
            'account_name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required|alpha_num||size:11',
        ];
    }
}

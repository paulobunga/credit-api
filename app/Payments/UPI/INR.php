<?php

namespace App\Payments\UPI;

class INR
{
    public $primary = 'upi_id';

    public $attributes = [
        'upi_id'
    ];

    public function rules()
    {
        return [
            'upi_id' => 'required',
        ];
    }
}

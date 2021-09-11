<?php

namespace App\Payments\NETBANK;

use Illuminate\Validation\Rule;

class VND
{
    public $primary = 'account_number';

    public $attributes = [
        'account_name',
        'account_number',
        'bank_name'
    ];

    public function rules()
    {
        return [
            'account_name' => 'required',
            'account_number' => 'required',
            'bank_name' => [
                'required',
                Rule::exists('banks', 'name')->where(function ($query) {
                    return $query->where('currency', 'VND');
                }),
            ]
        ];
    }
}

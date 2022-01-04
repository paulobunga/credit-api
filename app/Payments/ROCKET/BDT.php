<?php

namespace App\Payments\ROCKET;

class BDT
{
    public $primary = 'wallet_number';

    public $attributes = [
        'wallet_number'
    ];

    public $sms_rule = '/A\/C: (\d+)* Tk([\d,]+\.\d{2})* Comm:Tk([\d,]+\.\d{2})*; A\/C Balance: Tk([\d,]+\.\d{2})*.TxnId: (\w+)*/';

    public $sms_map = [
        'amount' => 2,
        'payer' => 1,
        'commission' => 3,
        'balance' => 4,
        'trx_id' => 5,
    ];

    public function rules()
    {
        return [
            'wallet_number' => 'required|regex:/^0\d{11}$/i',
        ];
    }

    public function extractSMS(string $sms)
    {
        preg_match($this->sms_rule, $sms, $matches, PREG_UNMATCHED_AS_NULL);

        return [
            'amount' => isset($matches[2]) ? str_replace(',', '', $matches[2]) : null,
            'payer' => isset($matches[1]) ? $matches[1] : null,
            'commission' => isset($matches[3]) ? str_replace(',', '', $matches[3]) : null,
            'balance' => isset($matches[4]) ? str_replace(',', '', $matches[4]) : null,
            'trx_id' => isset($matches[5]) ? $matches[5] : null,
        ];
    }
}

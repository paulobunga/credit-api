<?php

namespace App\Payments\BKASH;

class BDT
{
    public $primary = 'wallet_number';

    public $regex = [
        'payer' => '0\d{10}'
    ];

    public $attributes = [
        'wallet_number'
    ];

    public $sms_rule = '/Tk ([\d,]+\.\d{2})* from (\d+)*.+Tk ([\d,]+\.\d{2})*. Balance Tk ([\d,]+\.\d{2})*. TrxID (\w+)*/';

    public $sms_map = [
        'amount' => 1,
        'payer' => 2,
        'commission' => 3,
        'balance' => 4,
        'trx_id' => 5,
    ];

    public function rules()
    {
        return [
            'wallet_number' => 'required|regex:/^0\d{10}$/i',
        ];
    }

    public function extractSMS(string $sms)
    {
        preg_match($this->sms_rule, $sms, $matches, PREG_UNMATCHED_AS_NULL);

        return [
            'amount' => isset($matches[1]) ? str_replace(',', '', $matches[1]) : null,
            'payer' => isset($matches[2]) ? $matches[2] : null,
            'commission' => isset($matches[3]) ? str_replace(',', '', $matches[3]) : null,
            'balance' => isset($matches[4]) ? str_replace(',', '', $matches[4]) : null,
            'trx_id' => isset($matches[5]) ? $matches[5] : null,
        ];
    }
}

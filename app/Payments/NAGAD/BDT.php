<?php

namespace App\Payments\NAGAD;

class BDT
{
    public $primary = 'wallet_number';

    public $attributes = [
        'wallet_number'
    ];

    public $sms_rule = '/Tk ([\d,]+\.\d{2})* Customer: (\d+) TxnID: (\w+)* Comm: Tk ([\d,]+\.\d{2})* Balance: Tk ([\d,]+\.\d{2})* (\d{2}\/\d{2}\/\d{4} \d{2}:\d{2})/';

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
            'commission' => isset($matches[4]) ? str_replace(',', '', $matches[4]) : null,
            'balance' => isset($matches[5]) ? str_replace(',', '', $matches[5]) : null,
            'reference_id' => isset($matches[3]) ? $matches[3] : null,
            'date' => isset($matches[6]) ? $matches[6] : null
        ];
    }
}

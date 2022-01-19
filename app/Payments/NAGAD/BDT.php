<?php

namespace App\Payments\NAGAD;

class BDT
{
    public $primary = 'wallet_number';

    public $attributes = [
        'wallet_number'
    ];

    public $sms_rule = '/Tk ([\d,]+\.\d{2})*[\n|\s]Customer: (\d+)*[\n|\s]TxnID: (\w+)*[\n|\s]Comm: Tk ([\d,]+\.\d{2})*[\n|\s]Balance: Tk ([\d,]+\.\d{2})*/';

    public $sms_map = [
        'amount' => 1,
        'payer' => 2,
        'commission' => 4,
        'balance' => 5,
        'trx_id' => 3,
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
            'commission' => isset($matches[4]) ? str_replace(',', '', $matches[4]) : null,
            'balance' => isset($matches[5]) ? str_replace(',', '', $matches[5]) : null,
            'trx_id' => isset($matches[3]) ? $matches[3] : null
        ];
    }
}

<?php

namespace App\Observers;

trait ResellerBankCardObserver
{
    public static function validateAttribute(string $channel, string $currency, array $attributes, $ignore = 0)
    {
        $bankcard = null;
        switch ($channel) {
            case 'UPI':
                $bankcard = static::where('attributes->upi_id', $attributes['upi_id'])->first();
                break;
            case 'NETBANK':
                if ($currency == 'INR') {
                    $bankcard = static::where([
                        'attributes->account_number' => $attributes['account_number'],
                        'attributes->ifsc_code' => $attributes['ifsc_code'],
                    ])->first();
                }
                break;
            case 'BKASH':
            case 'NAGAD':
            case 'ROCKET':
            case 'UPAY':
                $bankcard = static::where('attributes->wallet_number', $attributes['wallet_number'])->first();
                break;
        }
        if ($bankcard && $bankcard->id != $ignore) {
            throw new \Exception('Bankcard is existed!');
        }
    }
}

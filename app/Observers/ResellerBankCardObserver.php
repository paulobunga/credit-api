<?php

namespace App\Observers;

use \App\Models\PaymentChannel;

trait ResellerBankCardObserver
{
    public static function validateAttribute(PaymentChannel $channel, string $currency, array $attributes, $ignore = 0)
    {
        $bankcard = null;
        switch ($channel->name) {
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
                $bankcard = static::where([
                    'attributes->wallet_number' =>  $attributes['wallet_number'],
                    'payment_channel_id' => $channel->id
                ])->first();
                break;
        }
        if ($bankcard && $bankcard->id != $ignore) {
            throw new \Exception('Bankcard is existed!');
        }
    }
}

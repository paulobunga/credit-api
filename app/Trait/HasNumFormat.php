<?php

namespace App\Trait;

use App\Models\Transaction;
use App\Models\MerchantDeposit;
use App\Models\MerchantWithdrawal;

trait HasNumFormat
{
    protected function toCurrencyFormat($value)
    {
        $currency = $this->attributes['currency'] ?? $this->reseller->currency;
        switch ($currency) {
            case 'INR':
                return number_format($value, 2, '.', '');
            case 'BDT':
                return number_format($value, 0, '.', '');
            default:
                return number_format($value, 2, '.', '');
        }
    }

    public function getAmountAttribute($value)
    {
        return $this->toCurrencyFormat($value);
    }

    public function getEarnAttribute()
    {
        $class = $this->getMorphClass();
        switch ($class) {
            case 'merchant.deposit':
                switch ($this->attributes['status']) {
                    case MerchantDeposit::STATUS['PENDING']:
                        $amount = $this->amount * $this->reseller->payin->commission_percentage ?? 0;
                        return $this->toCurrencyFormat($amount);
                    case MerchantDeposit::STATUS['APPROVED']:
                    case MerchantDeposit::STATUS['ENFORCED']:
                        $amount = $this->transactions()->where([
                            'user_type' => 'reseller',
                            'user_id' => $this->reseller->id,
                            'type' => Transaction::TYPE['SYSTEM_TOPUP_COMMISSION']
                        ])->first()->amount ?? 0;
                        return $this->toCurrencyFormat($amount);
                }
                break;
            case 'merchant.withdrawal':
                switch ($this->attributes['status']) {
                    case MerchantWithdrawal::STATUS['PENDING']:
                    case MerchantWithdrawal::STATUS['FINISHED']:
                        $amount = $this->amount * $this->reseller->payin->commission_percentage ?? 0;
                        return $this->toCurrencyFormat($amount);
                    case MerchantWithdrawal::STATUS['APPROVED']:
                        $amount = $this->transactions()->where([
                            'user_type' => 'reseller',
                            'user_id' => $this->reseller->id,
                            'type' => Transaction::TYPE['SYSTEM_TOPUP_COMMISSION']
                        ])->first()->amount ?? 0;
                        return $this->toCurrencyFormat($amount);
                }
                break;
        }
        return 0;
    }
}

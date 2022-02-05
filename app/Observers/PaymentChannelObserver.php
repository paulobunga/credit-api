<?php

namespace App\Observers;

use Illuminate\Support\Arr;
use App\Exceptions\ValidationHttpException;

trait PaymentChannelObserver
{
    protected $reference;

    public function validate(array $request)
    {
        if (!$this->attributes['name']) {
            throw new \Exception('Payment Channel name is null', 405);
        }

        if (!$this->attributes['currency']) {
            throw new \Exception('Payment Channel currency is null', 405);
        }

        $this->reference = $this->getReference();

        $request = Arr::only($request, $this->reference->attributes);

        $validator = app('validator')->make($request, $this->reference->rules(), [
            'wallet_number.regex' => 'incorrect mobile number format'
        ]);

        if ($validator->fails()) {
            throw new ValidationHttpException(
                $validator->errors()
            );
        }

        return $request;
    }

    /**
     * check whether payment channel support SMS auto approval or not
     *
     * @return bool
     */
    public function isSupportSMS()
    {
        return method_exists($this->getReference(), 'extractSMS');
    }

    /**
     * Get object reference by currency and name
     *
     * @return mixed
     */
    public function getReference()
    {
        if ($this->reference) {
            return $this->reference;
        }
        $objName = "\\App\\Payments\\{$this->name}\\{$this->currency}";
        if (!class_exists($objName)) {
            throw new \Exception("{$objName} is unsupported!");
        }
        return new $objName();
    }

    /**
     * Match payin order based on its currency
     *
     * @param  App\Models\MerchantDeposit $deposit
     * @return void
     */
    public function matchPayin($deposit)
    {
        if (method_exists($this->getReference(), 'matchPayin')) {
            return $this->getReference()->matchPayin($deposit, $this);
        }
        $objName = "\\App\\Payments\\{$deposit->currency}";
        if (method_exists($objName, 'matchPayin')) {
            return $objName::matchPayin($deposit, $this);
        }
    }
}

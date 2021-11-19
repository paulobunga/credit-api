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

        $validator = app('validator')->make($request, $this->reference->rules());

        if ($validator->fails()) {
            throw new ValidationHttpException(
                $validator->errors()
            );
        }

        return $request;
    }

    /**
     * Get data from SMS
     *
     * @param  string $sms
     * @return array
     */
    public function extractSMS(string $sms)
    {
        $this->reference = $this->getReference();
        if (!method_exists($this->reference, 'extractSMS')) {
            return [];
        }
        return $this->reference->extractSMS($sms);
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
            throw new \Exception("{$objName} is not unsupported!");
        }
        return new $objName();
    }
}

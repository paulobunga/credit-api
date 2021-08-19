<?php

namespace App\Observers;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Exceptions\ValidationHttpException;

trait PaymentChannelObserver
{

    public function validate(array $request)
    {
        if (!$this->attributes['name']) {
            throw new \Exception('Payment Channel name is null', 405);
        }

        if (!$this->attributes['currency']) {
            throw new \Exception('Payment Channel currency is null', 405);
        }
        $attributes = json_decode($this->attributes['attributes'], true);
        if (empty($attributes)) {
            throw new \Exception('Payment Channel attributes is empty', 405);
        }
        
        $request = Arr::only($request, $attributes);

        $name = strtoupper($this->name) . '_' . strtoupper($this->currency);
        switch ($name) {
            case 'NETBANK_INR':
                $rules = [
                    'account_name' => 'required',
                    'account_number' => 'required',
                    'ifsc_code' => 'required|digits:11',
                ];
                break;
            case 'UPI_INR':
                $rules = [
                    'upi_id' => 'required',
                ];
                break;
            case 'NETBANK_VND':
                $rules = [
                    'account_name' => 'required',
                    'account_number' => 'required',
                    'bank_name' => [
                        'required',
                        Rule::exists('banks', 'name')->where(function ($query) use ($request) {
                            return $query->where('currency', 'VND');
                        }),
                    ]
                ];
                break;
            case 'MOMOPAY_VND':
                $rules = [
                    'qrcode' => 'required',
                ];
                break;
            case 'ZALOPAY_VND':
                $rules = [
                    'qrcode' => 'required',
                ];
                break;
            case 'VIETTELPAY_VND':
                $rules = [
                    'qrcode' => 'required',
                ];
                break;
            default:
                throw new \Exception("{$name} is unsupported!");
        }
        $validator = app('validator')->make($request, $rules);

        if ($validator->fails()) {
            throw new ValidationHttpException(
                $validator->errors()
            );
        }

        return $request;
    }
}

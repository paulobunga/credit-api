<?php

namespace App\Trait;

use Dingo\Api\Http\Request;

trait SignValidator
{
    protected $merchant_class = \App\Models\Merchant::class;

    protected function validateSign(Request $request)
    {
        $this->validate($request, [
            'merchant_id' => 'required',
            'sign' => 'required'
        ], [
            'sign.required' => 'sign is required'
        ]);
        $merchant = $this->merchant_class::where('merchant_id', $request->merchant_id)->firstOrFail();
        $sign = $this->createSign($request->all(), $merchant->api_key);
        if ($sign !== $request->sign) {
            if (env('APP_ENV') == 'local') {
                throw new \Exception("request sign is invalid, {$sign}", 444);
            } else {
                throw new \Exception('request sign is invalid', 444);
            }
        }

        return $merchant;
    }

    protected function createSign(array $data, String $key)
    {
        ksort($data);
        $str = '';

        foreach ($data as $key => $val) {
            if ($key == 'sign') {
                continue;
            }
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val);
            }
            $str .= "{$key}={$val}&";
        }
        $str .= 'api_key=' .  $key;
        // dd($str);
        return md5($str);
    }
}

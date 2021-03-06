<?php

namespace App\Trait;

use Dingo\Api\Http\Request;
use App\Models\Merchant;

trait SignValidator
{

    protected function validateSign(Request $request, array $only = [])
    {
        $this->validate($request, [
            'uuid' => 'required|exists:merchants,uuid',
            'sign' => 'required'
        ], [
            'sign.required' => 'sign is required'
        ]);
        $merchant = Merchant::where('uuid', $request->uuid)->firstOrFail();
        if (empty($only)) {
            $sign = $this->createSign($request->all(), $merchant->api_key);
        } else {
            $sign = $this->createSign($request->only(['uuid', ...$only]), $merchant->api_key);
        }
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

        foreach ($data as $k => $val) {
            if ($k == 'sign') {
                continue;
            }
            if (is_null($val) || $val === '') {
                continue;
            }
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val);
            }
            $str .= "{$k}={$val}&";
        }
        $str .= 'api_key=' .  $key;

        return md5($str);
    }
}

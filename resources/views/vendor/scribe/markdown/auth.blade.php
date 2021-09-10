# Authenticating requests

@if(!$isAuthed)
This API is not authenticated.
@else
<p>This API is authenticated by sending parameters
    <b>uuid</b> and <b>sign</b>
    in the <b>body</b> or <b>query</b> of the request.
</p>
<p>All authenticated endpoints are marked with a
    <strong>requires authentication</strong> badge in the documentation below.
</p>
<p>You can create the sign value by the following Pseudo code.</p>
<pre>
    <code>
    /**
     * @param string $key The API Key of merchant
     * @param array  $data The json data of Http request
     * 
     * /
    protected function createSign(array $data, String $key)
    {
        ksort($data);
        $str = "";
        foreach ($data as $k => $val) {
            if ($k == "sign") {
                continue;
            }
            if (is_null($val) || $val === "") {
                continue;
            }
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val);
            }
            $str .= "{$k}={$val}&";
        }
        $str .= "api_key=" .  $key;

        return md5($str);
    }

    /**
     * @param string $key The API Key of merchant
     * @param array  $data The json data of Http request
     *
     * /
    protected function validateSign(array $data, String $key)
    {
        return $data['sign'] === $this->createSign($data, $key);
    }
    </code>
</pre>
{!! $extraAuthInfo !!}
@endif
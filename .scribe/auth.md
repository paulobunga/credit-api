# Authenticating requests

<p>This API is authenticated by sending a parameter
    <strong><code>sign</code></strong>
    in the <b>body</b> or <b>query</b> of the request.
</p>
<p>All authenticated endpoints are marked with a
    <strong>requires authentication</strong> badge in the documentation below.
</p>
You can create the sign value by the following Pseudo code.
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
        foreach ($data as $key => $val) {
            if ($key == "sign") {
                continue;
            }
            if (is_null($val) || $val === "") {
                continue;
            }
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val);
            }
            $str .= "{$key}={$val}&";
        }
        $str .= "api_key=" .  $key;

        return md5($str);
    }
    </code>
</pre>

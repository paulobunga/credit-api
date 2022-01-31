<?php

use Laravel\Lumen\Routing\UrlGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return rtrim(app()->basePath('public/' . $path), DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('mix')) {
    /**
     * Get the path to a versioned Mix file.
     *
     * @param  string  $path
     * @param  string  $manifestDirectory
     * @return \Illuminate\Support\HtmlString
     *
     * @throws \Exception
     */
    function mix($path, $manifestDirectory = '')
    {
        static $manifests = [];
        if (!Str::startsWith($path, '/')) {
            $path = "/{$path}";
        }
        if ($manifestDirectory && !Str::startsWith($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }
        $manifestKey = $manifestDirectory ? $manifestDirectory : '/';
        if (file_exists(public_path($manifestDirectory . 'hot'))) {
            return new HtmlString("//localhost:8080{$path}");
        }
        if (in_array($manifestKey, $manifests)) {
            $manifest = $manifests[$manifestKey];
        } else {
            if (!file_exists($manifestPath = public_path($manifestDirectory . '/mix-manifest.json'))) {
                throw new Exception('The Mix manifest does not exist.');
            }
            $manifests[$manifestKey] = $manifest = json_decode(
                file_get_contents($manifestPath),
                true
            );
        }
        if (!array_key_exists($path, $manifest)) {
            throw new Exception(
                "Unable to locate Mix file: {$path}. Please check your " .
                    'webpack.mix.js output paths and try again.'
            );
        }
        return new HtmlString($manifestDirectory . $manifest[$path]);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool $secure
     *
     * @return string
     */
    function asset($path, $secure = null)
    {
        return (new UrlGenerator(app()))->to($path, null, $secure);
    }
}

if (!function_exists('apiRoute')) {
    /**
     * Generate a URL to a named route.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool|null  $secure
     * @return string
     */
    function apiRoute($name, $parameters = [], $secure = true)
    {
        return app('api.url')->version(env('API_VERSION'))->route($name, $parameters, $secure);
    }
}

if (!function_exists('admin_url')) {
    /**
     * Generate an asset path to admin panel.
     *
     * @param  string $path
     * @return string
     */
    function admin_url($path = '')
    {
        return str_replace('//api', '//admin', env('APP_URL')) . $path;
    }
}

if (!function_exists('reseller_url')) {
    /**
     * Generate an asset path to agent panel.
     *
     * @param  string $path
     * @return string
     */
    function reseller_url($path = '')
    {
        return env('AGENT_URL') . $path;
    }
}

if (!function_exists('merchant_url')) {
    /**
     * Generate an asset path to merchant panel.
     *
     * @param  string $path
     * @return string
     */
    function merchant_url($path = '')
    {
        return str_replace('//api', '//merchant', env('APP_URL')) . $path;
    }
}

if (!function_exists('internal_gateway_ip')) {
    /**
     * Get backend internal gateway ip address
     *
     * @return string
     */
    function internal_gateway_ip(): string
    {
        $ip = explode('.', exec('grep "`hostname`" /etc/hosts|awk \'{print $1}\' | head -1'));
        array_pop($ip);
        $ip[] = 1;
        return implode('.', $ip);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Merchant;

class WhiteList
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if ($guard == null) {
            return $next($request);
        }

        if ($guard == 'merchant_api') {
            $uuid = $request->get('uuid', '');
            if (empty($uuid)) {
                throw new \Exception('uuid field is required', 405);
            }
            $merchant = Merchant::where('uuid', $uuid)->firstOrFail();
            $white_lists = $merchant->WhiteList->api ?? [];
        } elseif ($guard == 'merchant_backend') {
            $white_lists = auth()->user()->WhiteList->backend ?? [];
        } elseif ($guard == 'admin' && !auth()->user()->isSuperAdmin) {
            $white_lists = app(\App\Settings\AdminSetting::class)->white_lists;
        } else {
            return $next($request);
        }

        if (!in_array($request->ip(), $white_lists)) {
            Log::error($request->ip() . " is not in {$guard} white list" . json_encode($white_lists));
            throw new UnauthorizedHttpException('WhiteList', 'Unauthorized IP Address!');
        }

        return $next($request);
    }
}

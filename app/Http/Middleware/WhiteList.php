<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
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
    public function handle($request, Closure $next, $guard = null)
    {
        if ($guard == null) {
            return $next($request);
        }

        if ($guard == 'merchant_api') {
            $white_lists = Merchant::where(
                'uuid',
                $request->uuid
            )->first()->WhiteList->api ?? [];
        } elseif ($guard == 'merchant_backend') {
            $white_lists = Auth::user()->WhiteList->backend ?? [];
        } elseif ($guard == 'admin' && !Auth::user()->hasRole('Super Admin')) {
            $white_lists = app(\App\Settings\AdminSetting::class)->white_lists;
        } else {
            return $next($request);
        }

        if (!in_array($request->ip(), $white_lists)) {
            \Log::error($request->ip() . " is not in {$guard} white list" . json_encode($white_lists));
            throw new UnauthorizedHttpException('WhiteList', 'Unauthorized IP Address!');
        }

        return $next($request);
    }
}

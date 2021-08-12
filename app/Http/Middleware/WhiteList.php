<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\MerchantWhiteList;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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

        if ($guard == 'merchant') {
            $white_lists = MerchantWhiteList::where(
                'merchant_id',
                Auth::id()
            )->first()->ip ?? [];
        } elseif ($guard == 'admin') {
            if (Auth::user()->hasRole('Super Admin')) {
                return $next($request);
            }
            $white_lists = app(\App\Settings\AdminSetting::class)->white_lists;
        } else {
            return $next($request);
        }

        if (!in_array($request->ip(), $white_lists)) {
            \Log::error($request->ip() . " is not in {$guard}[" . Auth::id() . '] white list ' . json_encode($white_lists) . '.');
            throw new UnauthorizedHttpException('WhiteList', 'Unauthorized IP Address!');
        }

        return $next($request);
    }
}

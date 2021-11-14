<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDomain
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     * @param string $domain
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $domains = null)
    {
        $domains = explode('|', $domains);
        if (!in_array($request->getHost(), $domains)) {
            throw new \App\Exceptions\RouteNotFoundException();
        }

        return $next($request);
    }
}

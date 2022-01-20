<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Gate;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission, $guard = null)
    {
        $authGuard = auth()->guard($guard);
        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        if ($authGuard->user()->hasAnyRole($rolesOrPermissions)) {
            return $next($request);
        }

        $route = $request->route()[1]['as'] ?? null;
        if ($route && $authGuard->user()->can($route)) {
            return $next($request);
        }

        throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
    }
}

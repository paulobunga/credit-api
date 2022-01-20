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
        if ($routepermission = $request->route()[1]['as'] ?? null) {
            $rolesOrPermissions[] = $routepermission;
        }

        if ($authGuard->user()->hasAnyRole($rolesOrPermissions)) {
            return $next($request);
        }

        if ($authGuard->user()->hasAnyPermission($rolesOrPermissions)) {
            return $next($request);
        }
        
        if ($request->user()->can('index', [Role::class, $request->route()[1]['as']])) {
          return $next($request);
        }

        throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
    }
}

<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function index($user, $route)
    {
      return in_array($route, ['admin.roles.index']) && $user->hasAnyPermission(['admin.admins.store', 'admin.admins.update']);
    }
}

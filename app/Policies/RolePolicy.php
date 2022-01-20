<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     *
     * @param  mixed  $user
     * @return mixed
     */
    public function index($user)
    {
        return $user->hasAnyPermission(['admin.admins.store', 'admin.admins.update']);
    }

    /**
     * Determine whether the user can view the role.
     *
     * @param  mixed  $user
     * @param  App\Models\Role  $role
     * @return mixed
     */
    public function show($user, $role)
    {
        //
    }

    /**
     * Determine whether the user can create role.
     *
     * @param  mixed  $user
     * @return mixed
     */
    public function create($user)
    {
        //
    }

    /**
     * Determine whether the user can update the role.
     *
     * @param  mixed  $user
     * @param  App\Models\Role  $role
     * @return mixed
     */
    public function update($user, $role)
    {
        //
    }

    /**
     * Determine whether the user can delete the role.
     *
     * @param  mixed  $user
     * @param  App\Models\Role  $role
     * @return mixed
     */
    public function delete($user, $role)
    {
        //
    }
}

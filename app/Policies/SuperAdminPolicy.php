<?php

namespace App\Policies;

use App\Models\User;

class SuperAdminPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('roles.manage');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(
        User $user,
        User $model
    ): bool {
        return false;
    }
}

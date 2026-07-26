<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user): bool
    {
        return in_array(
            (int) $user->role_id,
            [1, 2],
            true
        );
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
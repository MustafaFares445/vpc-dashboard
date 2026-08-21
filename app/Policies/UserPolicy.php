<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->isSuperAdmin() || $user->is($model)) {
            return false;
        }

        if ($model->isSuperAdmin() && User::query()->where('is_super_admin', true)->where('is_active', true)->count() <= 1) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool { return $user->is_active; }
    public function view(User $user, Task $task): bool { return $user->isAdmin() || $task->assigned_to === $user->getKey(); }
    public function create(User $user): bool { return $user->is_active; }
    public function update(User $user, Task $task): bool { return $user->isAdmin() || $task->assigned_to === $user->getKey(); }
    public function delete(User $user, Task $task): bool { return $user->isAdmin(); }
    public function deleteAny(User $user): bool { return $user->isAdmin(); }
}

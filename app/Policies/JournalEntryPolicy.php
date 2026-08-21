<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.view');
    }

    public function view(User $user, JournalEntry $entry): bool
    {
        return $user->can('accounting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.manage');
    }

    public function update(User $user, JournalEntry $entry): bool
    {
        return false;
    }

    public function delete(User $user, JournalEntry $entry): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}

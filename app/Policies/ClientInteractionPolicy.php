<?php

namespace App\Policies;

use App\Models\ClientInteraction;
use App\Models\User;

class ClientInteractionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, ClientInteraction $interaction): bool
    {
        return $user->isAdmin() || $interaction->client?->assigned_to === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, ClientInteraction $interaction): bool
    {
        return $user->isAdmin() || $interaction->user_id === $user->getKey();
    }

    public function delete(User $user, ClientInteraction $interaction): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}

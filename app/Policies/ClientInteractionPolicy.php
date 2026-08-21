<?php

namespace App\Policies;

use App\Models\ClientInteraction;
use App\Models\User;

class ClientInteractionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('interactions.view');
    }

    public function view(User $user, ClientInteraction $interaction): bool
    {
        return $user->can('clients.manage') || ($user->can('interactions.view') && $interaction->client?->assigned_to === $user->getKey());
    }

    public function create(User $user): bool
    {
        return $user->can('interactions.create');
    }

    public function update(User $user, ClientInteraction $interaction): bool
    {
        return $user->can('clients.manage') || ($user->can('interactions.update') && $interaction->client?->assigned_to === $user->getKey());
    }

    public function delete(User $user, ClientInteraction $interaction): bool
    {
        return $user->can('interactions.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('interactions.delete');
    }
}

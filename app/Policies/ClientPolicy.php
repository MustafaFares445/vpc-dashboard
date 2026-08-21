<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clients.view');
    }

    public function view(User $user, Client $client): bool
    {
        return $user->can('clients.manage') || ($user->can('clients.view') && $client->assigned_to === $user->getKey());
    }

    public function create(User $user): bool
    {
        return $user->can('clients.manage');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->can('clients.manage');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->can('clients.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('clients.manage');
    }
}

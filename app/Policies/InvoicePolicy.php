<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('accounting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.manage');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('accounting.manage');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('accounting.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('accounting.manage');
    }
}

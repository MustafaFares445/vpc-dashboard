<?php

namespace App\Policies;

use App\Models\FinancialTransaction;
use App\Models\User;

class FinancialTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.view');
    }

    public function view(User $user, FinancialTransaction $transaction): bool
    {
        return $user->can('accounting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.manage');
    }

    public function update(User $user, FinancialTransaction $transaction): bool
    {
        return $user->can('accounting.manage');
    }

    public function delete(User $user, FinancialTransaction $transaction): bool
    {
        return $user->can('accounting.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('accounting.manage');
    }
}

<?php

namespace App\Policies;

use App\Models\FinancialTransaction;
use App\Models\User;

class FinancialTransactionPolicy
{
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, FinancialTransaction $transaction): bool { return $user->isAdmin(); }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, FinancialTransaction $transaction): bool { return $user->isAdmin(); }
    public function delete(User $user, FinancialTransaction $transaction): bool { return $user->isAdmin(); }
    public function deleteAny(User $user): bool { return $user->isAdmin(); }
}

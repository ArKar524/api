<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Verification;

class VerificationPolicy
{
    public function submitOwner(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function submitDriver(User $user): bool
    {
        return $user->role === 'driver';
    }

    public function review(User $user, Verification $verification): bool
    {
        return $user->role === 'admin';
    }
}

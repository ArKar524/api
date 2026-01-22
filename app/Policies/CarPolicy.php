<?php

namespace App\Policies;

use App\Models\Car;
use App\Models\User;

class CarPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function update(User $user, Car $car): bool
    {
        return $user->role === 'owner' && $car->owner_id === $user->id;
    }

    public function review(User $user, Car $car): bool
    {
        return $user->role === 'admin';
    }
}

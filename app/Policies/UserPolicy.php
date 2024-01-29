<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function admin(User $user): bool
    {
        return $user->role === 'owner' || $user->role === 'admin';
    }

    public function own(User $user): bool
    {
        return $user->id === auth()->id();
    }

}

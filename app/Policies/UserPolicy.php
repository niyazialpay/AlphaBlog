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

    public function owner(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function cloudflare(User $user): bool
    {
        return $user->role === 'owner' && ($user->webauthn === 1 || $user->otp === 1);
    }
}

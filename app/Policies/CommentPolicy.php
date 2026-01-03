<?php

namespace App\Policies;

use App\Models\User;

class CommentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner' || $user->role === 'admin' || $user->role === 'editor' || $user->role === 'author';
    }

    public function edit(User $user): bool
    {
        return $user->role === 'owner' || $user->role === 'admin' || $user->role === 'editor';
    }

    public function delete(User $user): bool
    {
        return $user->role === 'owner' || $user->role === 'admin' || $user->role === 'editor';
    }

    public function view(User $user): bool
    {
        return $user->role === 'owner' || $user->role === 'admin' || $user->role === 'editor';
    }
}

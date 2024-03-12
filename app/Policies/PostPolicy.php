<?php

namespace App\Policies;

use App\Models\Post\Posts;
use App\Models\User;

class PostPolicy
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
        if(request()->route()->parameter('type') == 'blogs') {
            return $user->role === 'owner' ||
                $user->role === 'admin' ||
                $user->role === 'editor' ||
                $user->role === 'author';
        }
        else{
            return $user->role === 'owner' ||
                $user->role === 'admin' ||
                $user->role === 'editor';
        }
    }

    public function createPost(User $user): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor' ||
            $user->role === 'author';
    }

    public function edit(User $user, Posts $posts): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor' ||
            ($user->id === $posts->user_id && $user->role === 'author');
    }

    public function delete(User $user, Posts $posts): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor' ||
            ($user->id === $posts->user_id && $user->role === 'author');
    }

    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Posts $posts): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor' ||
            ($user->id === $posts->user_id && $user->role === 'author');
    }

    public function viewPages(User $user): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor';
    }

    public function revert(User $user, Posts $posts): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor' ||
            ($user->id === $posts->user_id && $user->role === 'author');
    }

    public function category(User $user): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor';
    }
}

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
        if (request()->route()->parameter('type') == 'blogs') {
            return $user->role === 'owner' ||
                $user->role === 'admin' ||
                $user->role === 'editor' ||
                $user->role === 'author';
        } else {
            return $user->role === 'owner' ||
                $user->role === 'admin' ||
                $user->role === 'editor';
        }
    }

    public function moderator(User $user): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' || $user->role === 'editor';
    }

    public function createPost(User $user): bool
    {
        return $user->role === 'owner' ||
            $user->role === 'admin' ||
            $user->role === 'editor' ||
            $user->role === 'author';
    }

    /**
     * `$posts` NULL olabilir: route'lar bu yetenegi hem model bagli
     * (`->can('edit', 'post')`) hem SINIF seviyesinde
     * (`->can('edit', 'App\Models\Post\Posts')`, ornegin admin.post.index.bulk)
     * kullaniyor. Zorunlu parametre oldugunda sinif seviyesindeki cagri
     * TypeError firlatip ucu 500'e dusuruyordu.
     *
     * Sinif seviyesinde yazarlara IZIN VERILMEZ: toplu islemler tek tek sahiplik
     * dogrulamasi yapmiyor, bu yuzden kapi moderatorlerle sinirli tutulur.
     */
    public function edit(User $user, ?Posts $posts = null): bool
    {
        if ($user->role === 'owner' || $user->role === 'admin' || $user->role === 'editor') {
            return true;
        }

        return $posts !== null
            && $user->role === 'author'
            && $user->id === $posts->user_id;
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

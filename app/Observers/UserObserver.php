<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->notify(new \App\Notifications\VerifyEmail);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->email !== $user->getOriginal('email')) {
            $user->email_verified_at = null;
            $user->save();
            $user->notify(new \App\Notifications\VerifyEmail);
        }
    }
}

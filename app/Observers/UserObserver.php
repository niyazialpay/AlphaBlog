<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public static bool $emailFailed = false;

    public function created(User $user): void
    {
        self::$emailFailed = false;
        try {
            $user->notify(new VerifyEmail);
        } catch (\Throwable $e) {
            self::$emailFailed = true;
            Log::warning('[UserObserver] Verification email failed on created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updated(User $user): void
    {
        if ($user->email !== $user->getOriginal('email')) {
            $user->email_verified_at = null;
            $user->save();
            try {
                $user->notify(new VerifyEmail);
            } catch (\Throwable $e) {
                Log::warning('[UserObserver] Verification email failed on updated', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

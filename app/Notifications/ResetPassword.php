<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends ResetPasswordNotification
{
    public function toMail($notifiable): MailMessage
    {
        $locale = $notifiable->preferred_locale ?? app()->getLocale();
        app()->setLocale($locale);

        return (new MailMessage)
            ->subject(Lang::get('auth.reset_password.subject'))
            ->greeting(Lang::get('auth.reset_password.greeting'))
            ->line(Lang::get('auth.reset_password.line_1'))
            ->action(Lang::get('auth.reset_password.action'), url(config('app.url').route('password.reset', $this->token, false)))
            ->line(Lang::get('auth.reset_password.line_2', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('auth.reset_password.line_3'))
            ->salutation(Lang::get('auth.reset_password.salutation'));
    }
}

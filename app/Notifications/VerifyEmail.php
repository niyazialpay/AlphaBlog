<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmail extends VerifyEmailNotification
{
    public function toMail($notifiable)
    {
        $locale = $notifiable->preferred_locale ?? app()->getLocale();
        app()->setLocale($locale);

        return (new MailMessage)
            ->subject(Lang::get('auth.verify_email.subject'))
            ->greeting(Lang::get('auth.verify_email.greeting'))
            ->line(Lang::get('auth.verify_email.line_1'))
            ->action(Lang::get('auth.verify_email.action'), $this->verificationUrl($notifiable))
            ->line(Lang::get('auth.verify_email.line_2'))
            ->salutation(Lang::get('auth.verify_email.salutation'));
    }
}

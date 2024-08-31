<?php

namespace App\Notifications;

use App\Models\OneSignal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCommentNotificationToAdmin extends Notification
{
    use Queueable;

    private $postTitle;

    private $notificationMessage;

    private $notificationUrl;

    private $mailSubject;

    /**
     * Create a new notification instance.
     */
    public function __construct($postTitle, $notificationMessage, $notificationUrl, $mailSubject = 'New Comment')
    {
        $this->postTitle = $postTitle;
        $this->notificationMessage = $notificationMessage;
        $this->notificationUrl = $notificationUrl;
        $this->mailSubject = $mailSubject;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            'mail',
            'database',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->mailSubject)
            ->line($this->notificationMessage)
            ->action($this->postTitle, $this->notificationUrl);
    }

    public function toDatabase($notifiable): array
    {
        $onesignal = OneSignal::first();
        if ($onesignal) {
            OneSignal::sendPush(
                $this->postTitle,
                $this->notificationMessage,
                $this->notificationUrl,
                5
            );
        }

        return [
            'title' => $this->postTitle,
            'message' => $this->notificationMessage,
            'url' => $this->notificationUrl,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

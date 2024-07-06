<?php

namespace App\Notifications;

use App\Models\OneSignal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCommentNotificationToAdmin extends Notification
{
    use Queueable;

    private $postTitle;
    private $notificationMessage;
    private $notificationUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($postTitle, $notificationMessage, $notificationUrl)
    {
        $this->postTitle = $postTitle;
        $this->notificationMessage = $notificationMessage;
        $this->notificationUrl = $notificationUrl;
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
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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

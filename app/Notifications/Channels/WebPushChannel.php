<?php

namespace App\Notifications\Channels;

use App\Models\PushSubscription;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        $auth = $this->vapid();

        if ($auth === null) {
            return;
        }

        if (method_exists($notification, 'pushEvent')) {
            $event = $notification->pushEvent();

            if ($event !== null
                && method_exists($notifiable, 'wantsNotification')
                && ! $notifiable->wantsNotification($event, 'push')) {
                return;
            }
        }

        $subscriptions = $notifiable->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode($notification->toWebPush($notifiable), JSON_UNESCAPED_UNICODE);

        try {
            $webPush = new WebPush(['VAPID' => $auth]);
        } catch (Throwable $exception) {
            Log::error('WebPush baslatilamadi', ['exception' => $exception->getMessage()]);

            return;
        }

        foreach ($subscriptions as $subscription) {
            try {
                $webPush->queueNotification($this->toSubscription($subscription), $payload);
            } catch (Throwable $exception) {
                Log::warning('Push abonelik kaydi okunamadi', [
                    'subscription_id' => $subscription->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        $prune = (bool) config('webpush.prune_expired', true);

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                continue;
            }

            if ($prune && $report->isSubscriptionExpired()) {
                PushSubscription::query()
                    ->where('endpoint_hash', PushSubscription::hashFor($report->getEndpoint()))
                    ->delete();

                continue;
            }

            Log::warning('Push gonderilemedi', [
                'endpoint' => $report->getEndpoint(),
                'reason' => $report->getReason(),
            ]);
        }
    }

    /**
     * @return array<string, string>|null
     */
    private function vapid(): ?array
    {
        $public = config('webpush.public_key');
        $private = config('webpush.private_key');

        if (blank($public) || blank($private)) {
            return null;
        }

        return [
            'subject' => (string) config('webpush.subject', config('app.url')),
            'publicKey' => $public,
            'privateKey' => $private,
        ];
    }

    private function toSubscription(PushSubscription $subscription): Subscription
    {
        return Subscription::create([
            'endpoint' => $subscription->endpoint,
            'publicKey' => $subscription->public_key,
            'authToken' => $subscription->auth_token,
            'contentEncoding' => $subscription->content_encoding ?: 'aesgcm',
        ]);
    }
}

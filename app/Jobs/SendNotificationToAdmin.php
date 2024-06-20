<?php

namespace App\Jobs;

use App\Models\OneSignal;
use App\Models\Post\Posts;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationToAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $title;
    private string $message;
    private string $url;

    /**
     * Create a new job instance.
     */
    public function __construct($title, $message, $url = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Execute the job.
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $onesignal = OneSignal::first();
        if ($onesignal) {
            OneSignal::sendPush(
                $this->title,
                $this->message,
                $this->url,
                5
            );
        }
    }
}

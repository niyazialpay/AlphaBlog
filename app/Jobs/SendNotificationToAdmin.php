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

    private int $post_id;

    /**
     * Create a new job instance.
     */
    public function __construct($post_id)
    {
        $this->post_id = $post_id;
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
                Posts::find($this->post_id)->title,
                __('comments.new_comment_notification'),
                route('admin.post.edit', ['blogs', $this->post_id]),
                5
            );
        }
    }
}

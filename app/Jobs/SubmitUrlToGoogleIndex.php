<?php

namespace App\Jobs;

use App\Actions\GoogleIndexingAction;
use App\Models\Post\PostIndexingLog;
use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SubmitUrlToGoogleIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Posts $post;

    protected string $type;

    protected bool $force;

    public function __construct(Posts $post, string $type = 'URL_UPDATED', bool $force = false)
    {
        $this->post = $post;
        $this->type = $type;
        $this->force = $force;
    }

    public function handle(GoogleIndexingAction $action): void
    {
        $settings = GeneralSettings::first();

        if (! $settings?->google_indexing_enabled) {
            return;
        }

        if (! $this->force && $this->post->isIndexed()) {
            return;
        }

        if ($action->dailyQuotaReached()) {
            self::dispatch($this->post, $this->type, $this->force)
                ->delay(now()->addDay()->startOfDay());

            return;
        }

        $url = $this->post->frontendUrl();
        $result = $action->submit($url, $this->type);

        PostIndexingLog::create([
            'post_id' => $this->post->id,
            'url' => $url,
            'type' => $this->type,
            'status' => $result['status'],
            'response_code' => $result['code'] ?: null,
            'message' => $result['message'],
        ]);
    }

    public function failed(Throwable $e): void
    {
        PostIndexingLog::create([
            'post_id' => $this->post->id,
            'url' => $this->post->frontendUrl(),
            'type' => $this->type,
            'status' => 'failed',
            'response_code' => null,
            'message' => $e->getMessage(),
        ]);
    }
}

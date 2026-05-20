<?php

namespace App\Http\Controllers\Admin\Post;

use App\Actions\GoogleIndexingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkIndexRequest;
use App\Jobs\SubmitUrlToGoogleIndex;
use App\Models\Post\PostIndexingLog;
use App\Models\Post\Posts;
use Illuminate\Http\JsonResponse;

class PostIndexingController extends Controller
{
    public function bulkIndex(BulkIndexRequest $request): JsonResponse
    {
        $candidates = Posts::whereIn('id', $request->post_ids)
            ->where('is_published', true)
            ->whereDoesntHave('indexingLogs', fn ($q) => $q->where('status', 'success')->where('type', 'URL_UPDATED'))
            ->get();

        $skipped = count($request->post_ids) - $candidates->count();

        foreach ($candidates as $post) {
            SubmitUrlToGoogleIndex::dispatch($post);
        }

        return response()->json([
            'status' => 'success',
            'queued' => $candidates->count(),
            'skipped' => $skipped,
        ]);
    }

    public function single(string $type, Posts $post): JsonResponse
    {
        SubmitUrlToGoogleIndex::dispatch($post, 'URL_UPDATED', true);

        return response()->json(['status' => 'success']);
    }

    public function status(string $type, Posts $post): JsonResponse
    {
        $cached = $post->indexingLogs()
            ->where('type', 'URL_INSPECTED')
            ->where('status', 'success')
            ->first();

        if ($cached) {
            return response()->json([
                'indexed' => true,
                'coverage_state' => $cached->message ?? 'Indexed',
                'verdict' => 'PASS',
                'last_crawl_time' => null,
                'error' => false,
                'from_cache' => true,
                'cached_at' => $cached->created_at,
            ]);
        }

        $result = app(GoogleIndexingAction::class)->inspect($post->frontendUrl());

        if (! $result['error'] && $result['indexed']) {
            PostIndexingLog::create([
                'post_id' => $post->id,
                'url' => $post->frontendUrl(),
                'type' => 'URL_INSPECTED',
                'status' => 'success',
                'response_code' => 200,
                'message' => $result['coverage_state'],
            ]);
        }

        return response()->json($result);
    }

    public function history(string $type, Posts $post): JsonResponse
    {
        return response()->json(
            $post->indexingLogs()
                ->select(['id', 'url', 'type', 'status', 'response_code', 'message', 'created_at'])
                ->get()
        );
    }
}

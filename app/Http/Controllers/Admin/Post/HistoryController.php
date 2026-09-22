<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Models\Post\PostHistory;
use App\Models\Post\Posts;
use App\Support\Panel\PanelResponse;
use Illuminate\Http\RedirectResponse;
use Qazd\TextDiff;
use Symfony\Component\HttpFoundation\Response;

class HistoryController extends Controller
{
    public function history($type, Posts $posts): Response
    {
        $posts->load('history');

        return PanelResponse::render(
            'Posts/History/Index',
            'panel.post.history.index',
            [
                'type' => $type,
                'post' => ['id' => $posts->id, 'title' => $posts->title],
                'history' => $posts->history
                    ->sortByDesc('created_at')
                    ->map(fn (PostHistory $item) => [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'createdAt' => $item->created_at?->toIso8601String(),
                    ])
                    ->values(),
            ],
            ['posts' => $posts, 'type' => $type],
        );
    }

    public function delete($type, Posts $posts, PostHistory $history): RedirectResponse
    {
        $history->forceDelete();

        return back()->with('success', __('general.deleted'));
    }

    public function show($type, Posts $posts, PostHistory $history): Response
    {
        $posts->load('history');
        $textDiff = new TextDiff;

        $title = $textDiff::render($history->title, $posts->title);
        $slug = $textDiff::render($history->slug, $posts->slug);
        $content = $textDiff::render($history->content, $posts->content);

        return PanelResponse::render(
            'Posts/History/Show',
            'panel.post.history.show',
            [
                'type' => $type,
                'post' => ['id' => $posts->id, 'title' => $posts->title],
                'history' => [
                    'id' => $history->id,
                    'createdAt' => $history->created_at?->toIso8601String(),
                ],
                'diff' => ['title' => $title, 'slug' => $slug, 'content' => $content],
            ],
            [
                'posts' => $posts,
                'type' => $type,
                'history' => $history,
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
            ],
        );
    }

    public function revert($type, Posts $posts, PostHistory $history): RedirectResponse
    {
        $posts->update([
            'title' => $history->title,
            'slug' => $history->slug,
            'content' => $history->content,
        ]);

        return back()->with('success', __('post.revert_success'));
    }
}

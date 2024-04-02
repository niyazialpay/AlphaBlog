<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Models\Post\PostHistory;
use App\Models\Post\Posts;
use Qazd\TextDiff;

class HistoryController extends Controller
{
    public function history($type, Posts $posts)
    {
        return view('panel.post.history.index', [
            'posts' => $posts->load('history'),
            'type' => $type,
        ]);
    }

    public function delete($type, Posts $posts, PostHistory $history)
    {
        $history->forceDelete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function show($type, Posts $posts, PostHistory $history)
    {
        $posts->load('history');
        $textDiff = new TextDiff();

        return view('panel.post.history.show', [
            'posts' => $posts,
            'type' => $type,
            'history' => $history,
            'title' => $textDiff::render($posts->title, $history->title),
            'slug' => $textDiff::render($posts->slug, $history->slug),
            'content' => $textDiff::render($posts->content, $history->content),
        ]);
    }

    public function revert($type, Posts $posts, PostHistory $history)
    {
        $posts->update([
            'title' => $history->title,
            'slug' => $history->slug,
            'content' => $history->content,
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }
}

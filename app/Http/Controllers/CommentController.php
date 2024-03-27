<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\OneSignal;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(CommentRequest $request)
    {
        $comment = new Comments();
        $comment->comment = $request->validated('comment');
        $comment->post_id = $request->validated('post_id');
        $comment->name = $request->validated('name');
        $comment->email = $request->validated('email');
        $comment->ip_address = $request->ip();
        $comment->user_agent = $request->userAgent();
        $comment->is_approved = false;

        if($comment->save()){
            $content = [
                session('language') => Posts::find($request->validated('post_id'))->title
            ];

            $title = [
                session('language') => __('comments.new_comment_notification'),
            ];

            //OneSignal::sendPush($content, $title, 10);
            return response()->json([
                'status' => 'success',
                'message' => __('comments.comment_saved')
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => __('comments.comment_error')
        ]);
    }
}

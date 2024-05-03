<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\OneSignal;
use App\Models\Post\Comments;
use App\Models\Post\Posts;

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

        if ($comment->save()) {
            $onesignal = OneSignal::first();
            if ($onesignal) {
                OneSignal::sendPush([
                    'en' => Posts::find($request->validated('post_id'))->title,
                ], [
                    'en' => __('comments.new_comment_notification'),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => __('comments.comment_saved'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('comments.comment_error'),
        ]);
    }
}

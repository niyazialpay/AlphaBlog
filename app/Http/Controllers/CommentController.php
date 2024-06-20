<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Jobs\SendNotificationToAdmin;
use App\Models\OneSignal;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use GuzzleHttp\Exception\GuzzleException;

class CommentController extends Controller
{
    /**
     * @throws GuzzleException
     */
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
            if(config('settings.notification_send_method') == 'directly'){
                $onesignal = OneSignal::first();
                if ($onesignal) {
                    OneSignal::sendPush(
                        Posts::find($request->validated('post_id'))->title,
                        __('comments.new_comment_notification'),
                        route('admin.post.edit', ['blogs', $request->validated('post_id')]),
                        5
                    );
                }
            }
            else{
                SendNotificationToAdmin::dispatch($request->validated('post_id'));
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

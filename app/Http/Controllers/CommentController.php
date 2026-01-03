<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Jobs\SendNotificationToAdmin;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Notifications\SendCommentNotificationToAdmin;
use GuzzleHttp\Exception\GuzzleException;

class CommentController extends Controller
{
    /**
     * @throws GuzzleException
     */
    public function store(CommentRequest $request, Comments $comments)
    {
        $comments->comment = $request->validated('comment');
        $comments->post_id = $request->validated('post_id');
        $comments->name = $request->validated('name');
        $comments->email = $request->validated('email');
        $comments->ip_address = $request->ip();
        $comments->user_agent = $request->userAgent();

        if ($comments->save()) {
            $post = Posts::find($request->validated('post_id'));
            if (config('settings.notification_send_method') == 'directly') {
                $notification = new SendCommentNotificationToAdmin(
                    $post->title,
                    __('comments.new_comment_notification'),
                    route('admin.post.edit', ['blogs', $request->validated('post_id')]),
                    __('messages.comment_subject')
                );
                foreach (User::whereIn('role', ['admin', 'owner'])->get() as $admin) {
                    $admin->notify($notification->locale($admin->preferredLocale()));
                }
            } else {
                SendNotificationToAdmin::dispatch(
                    Posts::find($request->validated('post_id'))->title,
                    __('comments.new_comment_notification'),
                    route('admin.post.edit', ['blogs', $request->validated('post_id')]),
                    __('messages.comment_subject')
                );
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

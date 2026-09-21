<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\CommentRequest;
use App\Models\Post\Comments;
use App\Models\User;
use App\Support\Panel\PanelResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $with = ['post', 'user'];
        if (GetPost($request->get('tab')) == 'trashed') {
            $comments = Comments::onlyTrashed()->with($with);
        } else {
            $comments = Comments::with($with);
            if ($request->get('search')) {
                $comments = $comments->where('comment', 'like', '%'.$request->get('search').'%');
            }
        }

        $paginator = $comments->orderBy('created_at', 'DESC')->paginate(10)->withQueryString();

        return PanelResponse::render(
            'Comments/Index',
            'panel.post.comments.index',
            [
                'type' => 'blogs',
                'comments' => PanelResponse::rows($paginator, fn (Comments $comment) => [
                    'id' => $comment->id,
                    'name' => $comment->name,
                    'email' => $comment->email,
                    'comment' => $comment->comment,
                    'is_approved' => (bool) $comment->is_approved,
                    'ip' => $comment->ip_address,
                    'user' => $comment->user ? ['id' => $comment->user->id, 'nickname' => $comment->user->nickname] : null,
                    'post' => $comment->post ? [
                        'id' => $comment->post->id,
                        'title' => $comment->post->title,
                        'type' => $comment->post->post_type === 'page' ? 'pages' : 'blogs',
                    ] : null,
                    'deleted_at' => $comment->deleted_at?->toIso8601String(),
                    'createdAt' => $comment->created_at?->toIso8601String(),
                ]),
                'users' => User::query()
                    ->orderBy('nickname')
                    ->get(['id', 'nickname'])
                    ->map(fn (User $user) => ['id' => (string) $user->id, 'nickname' => $user->nickname])
                    ->values(),
                'filters' => [
                    'search' => $request->get('search'),
                    'tab' => GetPost($request->get('tab')) === 'trashed' ? 'trashed' : 'all',
                ],
            ],
            [
                'comments' => $paginator,
                'users' => User::all(),
                'type' => 'blogs',
            ],
        );
    }

    public function edit(Comments $comment)
    {
        return response()->json($comment->load('post'));
    }

    public function approve(Comments $comment): RedirectResponse
    {
        $comment->is_approved = true;
        if ($comment->save()) {
            return back()->with('success', __('comments.success_approve'));
        }

        return back()->with('error', __('comments.error_approve'));
    }

    public function disapprove(Comments $comment): RedirectResponse
    {
        $comment->is_approved = false;
        if ($comment->save()) {
            return back()->with('success', __('comments.success_disapprove'));
        }

        return back()->with('error', __('comments.error_disapprove'));
    }

    public function delete(Comments $comment): RedirectResponse
    {
        if ($comment->delete()) {
            return back()->with('success', __('comments.success_delete'));
        }

        return back()->with('error', __('comments.error_delete'));
    }

    public function restore(Comments $comment): RedirectResponse
    {
        $comment->trashed();
        if ($comment->restore()) {
            return back()->with('success', __('comments.success_restore'));
        }

        return back()->with('error', __('comments.error_restore'));
    }

    public function forceDelete(Comments $comment): RedirectResponse
    {
        if ($comment->forceDelete()) {
            return back()->with('success', __('comments.success_force_delete'));
        }

        return back()->with('error', __('comments.error_force_delete'));
    }

    public function save(Comments $comment, CommentRequest $request): RedirectResponse
    {
        if (auth()->check() && ! $comment->id &&
            (
                auth()->user()->can('admin', auth()->user()) ||
                auth()->user()->can('owner', auth()->user()) ||
                auth()->user()->can('editor', auth()->user())
            )
        ) {
            $comment->is_approved = true;
            $comment->ip_address = $request->getClientIp();
            $comment->user_agent = $request->userAgent();
        }
        $comment->user_id = GetPost($request->user_id);
        $comment->name = $request->name;
        $comment->email = $request->email;
        $comment->comment = $request->comment;
        $comment->created_at = dateformat($request->post('created_date'), 'Y-m-d H:i:s', config('app.timezone'));
        $comment->post_id = GetPost($request->post_id);
        if ($comment->save()) {
            return back()->with('success', __('comments.success_save'));
        }

        return back()->with('error', __('comments.error_save'));
    }
}

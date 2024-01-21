<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comments;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index(Request $request){
        $with = ['post', 'user'];
        if(GetPost($request->get('tab'))=='trashed'){
            $comments = Comments::onlyTrashed()->with($with);
        }
        else{
            $comments = Comments::with($with);
            if($request->get('search')){
                $comments = $comments->where('comment', 'like', '%'.$request->get('search').'%');
            }
        }
        return view('panel.post.comments.index', [
            'comments' => $comments->paginate(10),
            'users' => User::all(),
            'type' => 'blogs'
        ]);
    }

    public function edit(Comments $comment){
        return response()->json($comment->load('post'));
    }

    public function approve(Comments $comment){
        $comment->is_approved = true;
        if($comment->save()){
            return response()->json(['status' => 'success', 'message' => __('comments.success_approve')]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('comments.error_approve')]);
        }
    }

    public function disapprove(Comments $comment){
        $comment->is_approved = false;
        if($comment->save()){
            return response()->json(['status' => 'success', 'message' => __('comments.success_disapprove')]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('comments.error_disapprove')]);
        }
    }

    public function delete(Comments $comment){
        if($comment->delete()){
            return response()->json(['status' => 'success', 'message' => __('comments.success_delete')]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('comments.error_delete')]);
        }
    }

    public function restore(Comments $comment){
        $comment->trashed();
        if($comment->restore()){
            return response()->json(['status' => 'success', 'message' => __('comments.success_restore')]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('comments.error_restore')]);
        }
    }

    public function forceDelete(Comments $comment){
        if($comment->forceDelete()){
            return response()->json(['status' => 'success', 'message' => __('comments.success_force_delete')]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('comments.error_force_delete')]);
        }
    }

    public function save(Comments $comment, CommentRequest $request){
        if(auth()->check() && !$comment->id){
            $comment->is_approved = true;
            $comment->ip_address = $request->getClientIp();
            $comment->user_agent = $request->userAgent();
        }
        $comment->user_id = GetPost($request->user_id);
        $comment->name = GetPost($request->name);
        $comment->email = GetPost($request->email);
        $comment->comment = GetPost($request->comment);
        $comment->created_at = GetPost($request->created_date);
        $comment->post_id = GetPost($request->post_id);
        if($comment->save()){
            return response()->json(['status' => 'success', 'message' => __('comments.success_save')]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('comments.error_save')]);
        }
    }
}

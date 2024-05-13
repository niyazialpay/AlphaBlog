<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostRequest;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;

class PostController extends Controller
{
    public function index(
        $type,
        Request $request,
        Posts $post,
        $category = null
    ): View|Application|Factory|\Illuminate\Contracts\Foundation\Application {
        if ($type == 'pages') {
            $type = 'pages';
            $post_type = 'page';
        } elseif ($type == 'blogs') {
            $type = 'blogs';
            $post_type = 'post';
        } else {
            abort(404);
        }
        $with = ['user', 'categories', 'comments'];
        if ($category && $post_type == 'post') {
            $posts = $post::search(GetPost($request->search))->query(function ($query) use ($with) {
                $query->with($with);
            })->where('category_id', $category)->where('post_type', $post_type);
        } else {
            $posts = $post::search(GetPost($request->search))->query(function ($query) use ($with) {
                $query->with($with);
            })->where('post_type', $post_type);
        }
        if (! (auth()->user()->role == 'owner' || auth()->user()->role == 'admin' || auth()->user()->role == 'editor')) {
            $posts = $posts->where('user_id', auth()->user()->id);
        }

        return view('panel.post.index', [
            'posts' => $posts->where('language', GetPost($request->get('language')))
                ->orderBy('created_at', 'desc')
                ->paginate(10),
            'trashed' => $post::onlyTrashed()->where('language', GetPost($request->get('language')))
                ->orderBy('created_at', 'desc')
                ->paginate(10),
            'type' => $type,
        ]);
    }

    public function create(
        $type,
        Posts $post,

    ): View|Application|Factory|\Illuminate\Contracts\Foundation\Application {
        if (! ($type == 'pages' || $type == 'blogs')) {
            abort(404);
        }
        if ($post->id) {
            $post->load([
                'categories',
                'user',
                'comments',
                'comments.user',
                'history',
            ]);
        }

        return view('panel.post.add-edit', [
            'post' => $post,
            'categories' => Categories::all(),
            'users' => User::all(),
            'type' => $type,
        ]);
    }

    public function save(
        $type,
        PostRequest $request,
        Posts $post
    ): JsonResponse {
        try {
            DB::beginTransaction();
            if ($post->id) {
                $message = __('post.success_update');
            } else {
                $message = __('post.success');
            }
            $post->title = GetPost($request->post('title'));
            if ($request->slug == null) {
                $slug = Str::slug($request->post('title'));
            } else {
                $slug = Str::slug($request->post('slug'));
            }
            $post->slug = $slug;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $post->addMediaFromRequest('image')->toMediaCollection('posts');
            }
            $post->content = content($request->post('content'));
            $post->meta_description = GetPost($request->post('meta_description'));
            $post->meta_keywords = content($request->post('meta_keywords'));
            $post->user_id = GetPost($request->post('user_id'));
            $post->is_published = $request->post('is_published') == 1;
            $post->post_type = GetPost($request->post('post_type'));
            $post->language = GetPost($request->post('language'));
            $post->created_at = dateformat($request->post('published_at'), 'Y-m-d H:i:s', config('app.timezone'));

            $hreflang = [];
            foreach ($request->hreflang_url as $key => $value) {
                if ($value != null) {
                    $hreflang[$key] = GetPost($value);
                }
            }
            $post->href_lang = json_encode($hreflang);

            if ($post->save()) {
                if ($request->post('post_type') == 'post') {
                    $post->categories()->sync($request->post('category_id'));
                }
                DB::commit();

                return response()->json(['status' => 'success', 'message' => $message, 'id' => $post->id]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('post.error')])->setStatusCode(500);
            }
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function delete($type, Posts $post)
    {
        try {
            DB::beginTransaction();
            if (! ($type == 'pages' || $type == 'blogs')) {
                abort(404);
            }
            if ($post->delete()) {
                Comments::where('post_id', $post->id)->delete();
                DB::commit();

                return response()->json(['status' => 'success', 'message' => __('post.success_delete')]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('post.post.error_delete')]);
            }
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function forceDelete($type, Posts $post)
    {
        try {
            DB::beginTransaction();
            if (! ($type == 'pages' || $type == 'blogs')) {
                abort(404);
            }
            $post->categories()->detach();
            foreach ($post->getMedia('*') as $media) {
                $media->delete();
            }
            Comments::where('post_id', $post->id)->forceDelete();
            if ($post->forceDelete()) {
                DB::commit();

                return response()->json(['status' => 'success', 'message' => __('post.post.success_force_delete')]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('post.post.error_force_delete')]);
            }
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function restore($type, Posts $post)
    {
        try {
            DB::beginTransaction();
            if (! ($type == 'pages' || $type == 'blogs')) {
                abort(404);
            }
            if ($post->restore()) {
                Comments::onlyTrashed()->where('post_id', $post->id)->restore();
                DB::commit();

                return response()->json(['status' => 'success', 'message' => __('post.post.success_restore')]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('post.post.error_restore')]);
            }
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function imageDelete($type, Posts $post)
    {
        $post->deleteMedia($post->getFirstMedia('posts'));

        return response()->json(['status' => true, 'message' => __('post.success_image_delete')]);
    }

    public function media($type, Posts $post)
    {
        return view('panel.post.media', [
            'post' => $post,
            'type' => $type,
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function editorImageUpload($type, Posts $post, Request $request)
    {
        if (! $post->id) {
            $post->title = GetPost($request->post('title')).' (draft)';
            if ($request->slug == null) {
                $slug = Str::slug($request->post('title'));
            } else {
                $slug = Str::slug($request->post('slug'));
            }
            $post->slug = $slug;
            $post->content = content($request->post('content'));
            $post->post_type = GetPost($request->post('post_type'));
            $post->language = $request->post('language');
            $post->user_id = auth()->user()->id;
            $post->meta_keywords = $request->post('meta_keywords');
            $post->save();
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $post->addMediaFromRequest('file')->toMediaCollection('content_images');
        }

        return response()->json([
            'success' => true,
            'blog_id' => $post->id,
            'location' => $post->getMedia('content_images')->last()->getFullUrl('resized'),
        ]);
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function postImageDelete($type, Posts $post, Request $request)
    {
        $post->deleteMedia($request->post('media_id'));

        return response()->json([
            'success' => true,
        ]);
    }
}

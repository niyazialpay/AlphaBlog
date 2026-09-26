<?php

namespace App\Http\Controllers\Admin\Post;

use App\Actions\CacheClear;
use App\Http\Controllers\Admin\AuthorSearchController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDeletePostsRequest;
use App\Http\Requests\Post\PostRequest;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Support\Panel\Panel;
use App\Support\Panel\PanelResponse;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class PostController extends Controller
{
    private function wantsDataTable(Request $request): bool
    {
        return $request->ajax()
            && ! $request->inertia()
            && $request->has('draw');
    }

    /**
     * @param  null  $category
     * @return SymfonyResponse
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function index(
        $type,
        Request $request,
        Posts $post,
        $category = null
    ) {
        if ($type == 'pages') {
            $type = 'pages';
            $post_type = 'page';
        } elseif ($type == 'blogs') {
            $type = 'blogs';
            $post_type = 'post';
        } else {
            abort(404);
        }
        $posts = $post::withCount('qrScans')->with(['user', 'categories', 'comments']);
        if ($category && $post_type == 'post') {
            $posts = $post->whereHas('categories', function ($query) use ($category) {
                return $query->where('category_id', $category);
            });
        }
        $posts = $posts->where('post_type', $post_type);
        if (! (auth()->user()->role == 'owner' || auth()->user()->role == 'admin' || auth()->user()->role == 'editor')) {
            $posts = $posts->where('user_id', auth()->user()->id);
        }

        if ($this->wantsDataTable($request)) {
            session()->remove('post_datatable_length');
            session()->put('post_datatable_length', $request->input('length'));

            $order = (string) $request->input('order.0.name', 'created_at');
            $dir = strtolower((string) $request->input('order.0.dir')) === 'asc' ? 'asc' : 'desc';

            $posts = $posts->where('language', GetPost($request->get('language')));

            if ($order == 'categories') {
                $posts = $posts->orderBy(function ($query) use ($dir) {
                    return $query->select('category_id')
                        ->from('post_categories')
                        ->whereColumn('post_categories.post_id', 'posts.id')
                        ->orderBy('post_categories.category_id', $dir)->limit(1);
                });
            } elseif ($order == 'user') {
                $posts = $posts->orderBy(function ($query) use ($dir) {
                    return $query->select('nickname')
                        ->from('users')
                        ->whereColumn('users.id', 'posts.user_id')
                        ->orderBy('users.nickname', $dir)->limit(1);
                });
            } else {
                if (! Schema::hasColumn((new Posts)->getTable(), $order)) {
                    $order = 'created_at';
                }
                $posts = $posts->orderBy($order, $dir);
            }

            return DataTables::eloquent($posts)
                ->enableScoutSearch(Posts::class)
                ->addColumn('checkbox', function ($post) {
                    return '<input type="checkbox" class="post-checkbox" value="'.$post->id.'">';
                })
                ->addColumn('user', function ($post) {
                    return $post->user ? $post->user->nickname : '';
                })
                ->addColumn('title', function ($post) use ($type) {
                    return view('panel.post.partials.title', compact('post', 'type'));
                })
                ->addColumn('categories', function ($post) use ($type) {
                    return view('panel.post.partials.categories', compact('post', 'type'));
                })
                ->addColumn('action', function ($post) use ($type) {
                    return view('panel.post.partials.actions', compact('post', 'type'));
                })
                ->addColumn('media', function ($post) use ($type) {
                    return view('panel.post.partials.media', compact('post', 'type'));
                })
                ->addColumn('created_at', function ($post) {
                    return dateformat($post->created_at, 'Y-m-d H:i:s', config('app.timezone'));
                })
                ->addColumn('updated_at', function ($post) {
                    return dateformat($post->updated_at, 'd.m.Y H:i:s', config('app.timezone'));
                })
                ->rawColumns(['action', 'checkbox'])
                ->make(true);
        }

        if ($category) {
            $datatable_url = route('admin.post.category', ['type' => $type, 'category' => $category]).'?tab='.request()->get('tab').'&language='.request()->get('language');
        } else {
            $datatable_url = route('admin.posts', ['type' => $type]).'?tab='.request()->get('tab').'&language='.request()->get('language');
        }

        $language = GetPost($request->get('language'));
        $tab = $request->get('tab') === 'trashed' ? 'trashed' : 'published';

        $trashed = $post::onlyTrashed()->where('language', $language)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'trashed_page')
            ->withQueryString();

        return PanelResponse::render(
            'Posts/Index',
            'panel.post.index',
            [
                'type' => $type,
                'category' => $category,
                'rows' => $this->rows($request, clone $posts, $language),
                'trashed' => PanelResponse::rows($trashed, fn (Posts $item) => $this->row($item)),
                'filters' => [
                    'search' => $request->get('search'),
                    'language' => $language,
                    'tab' => $tab,
                    'sort' => $this->sortColumn($request),
                    'dir' => $request->get('dir') === 'asc' ? 'asc' : 'desc',
                    'per_page' => $this->perPage($request),
                ],
            ],
            ['trashed' => $trashed, 'type' => $type, 'datatable_url' => $datatable_url],
        );
    }

    private function rows(Request $request, $query, ?string $language)
    {
        $search = trim((string) $request->get('search'));
        $perPage = $this->perPage($request);

        if ($search !== '') {
            $results = Posts::search($search)
                ->query(fn ($builder) => $builder->withCount('qrScans')->with(['user', 'categories', 'comments']))
                ->where('language', $language)
                ->paginate($perPage)
                ->withQueryString();

            return $results->through(fn (Posts $item) => $this->row($item));
        }

        $sort = $this->sortColumn($request);
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        return $query->where('language', $language)
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Posts $item) => $this->row($item));
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Posts $item): array
    {
        $user = auth()->user();

        return [
            'id' => $item->id,
            'title' => $item->title,
            'slug' => $item->slug,
            'language' => $item->language,
            'post_type' => $item->post_type,
            'is_published' => (bool) $item->is_published,
            'views' => (int) $item->views,
            'qr_scans_count' => (int) ($item->qr_scans_count ?? 0),
            'comments_count' => $item->relationLoaded('comments') ? $item->comments->count() : 0,
            'categories' => $item->relationLoaded('categories')
                ? $item->categories->map(fn ($category) => ['id' => $category->id, 'name' => $category->name])->values()
                : [],
            'author' => $item->user ? ['id' => $item->user->id, 'nickname' => $item->user->nickname] : null,
            'thumbnail' => mediaConversionUrl($item->getFirstMedia('posts'), 'resized'),
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
            'deleted_at' => $item->deleted_at?->toIso8601String(),
            'can' => [
                'edit' => $user?->can('edit', $item) ?? false,
                'delete' => $user?->can('delete', $item) ?? false,
            ],
        ];
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->get('per_page', 10);

        return in_array($perPage, [10, 25, 50, 75, 100], true) ? $perPage : 10;
    }

    private function sortColumn(Request $request): string
    {
        $sort = (string) $request->get('sort', 'created_at');

        return Schema::hasColumn((new Posts)->getTable(), $sort) ? $sort : 'created_at';
    }

    public function create(
        $type,
        Posts $post,
    ): SymfonyResponse {
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
            $post->loadCount('qrScans');
            $categories = Categories::where('language', $post?->language)->get();
        } else {
            $categories = Categories::where('language', session('language'))->get();
        }

        return PanelResponse::render(
            'Posts/Edit',
            'panel.post.add-edit',
            [
                'type' => $type,
                'post' => $this->editorPost($post),
                'categories' => $categories->map(fn (Categories $category) => [
                    'id' => (string) $category->id,
                    'name' => $category->name,
                    'language' => $category->language,
                ])->values(),
                'authorSeed' => ($author = $post->user ?? auth()->user())
                    ? [AuthorSearchController::option($author, auth()->user()->can('admin', User::class))]
                    : [],
                'languageOptions' => collect(app('languages'))
                    ->map(fn ($language) => ['code' => $language->code, 'name' => $language->name])
                    ->values(),
                'sessionLanguage' => session('language'),
            ],
            [
                'post' => $post,
                'categories' => $categories,
                'users' => Panel::vueEnabled('Posts/Edit') ? collect() : User::all(),
                'type' => $type,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function editorPost(Posts $post): array
    {
        $hreflang = $post->href_lang ? (json_decode($post->href_lang, true) ?: []) : [];

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'meta_keywords' => $post->meta_keywords,
            'meta_description' => $post->meta_description,
            'language' => $post->language,
            'post_type' => $post->post_type,
            'is_published' => (bool) $post->is_published,
            'user_id' => $post->user_id ? (string) $post->user_id : (string) auth()->id(),
            'category_ids' => $post->relationLoaded('categories')
                ? $post->categories->pluck('id')->map(fn ($id) => (string) $id)->values()
                : [],
            'hreflang' => $hreflang,
            'image' => mediaConversionUrl($post->getFirstMedia('posts'), 'resized'),
            'qr_link' => $post->qr_link ?? null,
            'qr_scans_count' => (int) ($post->qr_scans_count ?? 0),
            'qr_image' => self::qrImage($post->qr_link ?? null),
            'history_count' => $post->relationLoaded('history') ? $post->history->count() : 0,
            'comments_count' => $post->relationLoaded('comments') ? $post->comments->count() : 0,
            'published_at' => $post->created_at
                ? $post->created_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i')
                : now()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
        ];
    }

    private static function qrImage(?string $link): ?string
    {
        if (! $link) {
            return null;
        }

        try {
            $svg = (new Writer(
                new ImageRenderer(new RendererStyle(220, 0), new SvgImageBackEnd)
            ))->writeString($link);

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        } catch (Throwable) {
            return null;
        }
    }

    public function save(
        $type,
        PostRequest $request,
        Posts $post
    ): RedirectResponse {
        try {
            DB::beginTransaction();
            $isNewPost = ! $post->id;
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
                $ext = $request->file('image')->extension();
                $post->addMediaFromRequest('image')
                    ->usingFileName($slug.'-'.time().'.'.$ext)
                    ->toMediaCollection('posts');
            }
            $post->content = content($request->post('content'));
            $post->meta_description = GetPost($request->post('meta_description'));
            $post->meta_keywords = content($request->post('meta_keywords'));
            if (in_array(auth()->user()->role, ['owner', 'admin'], true)) {
                $post->user_id = GetPost($request->post('user_id')) ?: ($post->user_id ?? auth()->id());
            } else {
                $post->user_id = $post->user_id ?? auth()->id();
            }
            $post->is_published = $request->post('is_published') == 1;
            $post->post_type = GetPost($request->post('post_type'));
            $post->language = GetPost($request->post('language'));
            $post->created_at = dateformat($request->post('published_at'), 'Y-m-d H:i:s', config('app.timezone'));

            $hreflang = [];
            foreach ((array) $request->input('hreflang_url', []) as $key => $value) {
                if ($value != null) {
                    $hreflang[$key] = GetPost($value);
                }
            }
            $post->href_lang = json_encode($hreflang);

            if ($post->save()) {
                if ($request->post('post_type') == 'post') {
                    $post->categories()->sync($request->post('category_id'));
                }
                if ($isNewPost && $post->post_type === 'post' && ! $post->qr_link) {
                    $post->qr_link = config('app.url').'/'.$post->language.'/'.$post->slug.'/qr/'.Str::random(64);
                    $post->saveQuietly();
                }
                DB::commit();
                CacheClear::cacheClear();

                return to_route('admin.post.edit', ['type' => $type, 'post' => $post->id])
                    ->with('success', $message);
            }

            DB::rollBack();

            return back()->with('error', __('post.error'));
        } catch (Exception $exception) {
            DB::rollBack();

            return back()->with('error', $exception->getMessage());
        }
    }

    public function delete($type, Posts $post): RedirectResponse
    {
        try {
            DB::beginTransaction();
            if (! ($type == 'pages' || $type == 'blogs')) {
                abort(404);
            }
            if ($post->delete()) {
                Comments::where('post_id', $post->id)->delete();
                DB::commit();
                CacheClear::cacheClear();

                return back()->with('success', __('post.success_delete'));
            }

            DB::rollBack();

            return back()->with('error', __('post.post.error_delete'));
        } catch (Exception $exception) {
            DB::rollBack();

            return back()->with('error', $exception->getMessage());
        }
    }

    public function bulkDelete(BulkDeletePostsRequest $request, string $type): RedirectResponse
    {
        if (! in_array($type, ['pages', 'blogs'], true)) {
            abort(404);
        }

        $posts = Posts::query()->whereIn('id', $request->validated('post_ids'))->get();
        $deletable = $posts->filter(fn (Posts $post): bool => $request->user()->can('delete', $post));
        $skipped = $posts->count() - $deletable->count();

        if ($deletable->isEmpty()) {
            return back()->with('error', __('post.bulk_delete_none'));
        }

        try {
            DB::transaction(function () use ($deletable): void {
                $ids = $deletable->modelKeys();

                $deletable->each->delete();
                Comments::query()->whereIn('post_id', $ids)->delete();
            });
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        CacheClear::cacheClear();

        return back()->with('success', __('post.bulk_delete_result', [
            'deleted' => $deletable->count(),
            'skipped' => $skipped,
        ]));
    }

    public function forceDelete($type, Posts $post): RedirectResponse
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
                CacheClear::cacheClear();

                return back()->with('success', __('post.post.success_force_delete'));
            }

            DB::rollBack();

            return back()->with('error', __('post.post.error_force_delete'));
        } catch (Exception $exception) {
            DB::rollBack();

            return back()->with('error', $exception->getMessage());
        }
    }

    public function restore($type, Posts $post): RedirectResponse
    {
        try {
            DB::beginTransaction();
            if (! ($type == 'pages' || $type == 'blogs')) {
                abort(404);
            }
            if ($post->restore()) {
                Comments::onlyTrashed()->where('post_id', $post->id)->restore();
                DB::commit();
                CacheClear::cacheClear();

                return back()->with('success', __('post.post.success_restore'));
            }

            DB::rollBack();

            return back()->with('error', __('post.post.error_restore'));
        } catch (Exception $exception) {
            DB::rollBack();

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function imageDelete($type, Posts $post, Request $request): JsonResponse
    {
        $post->deleteMedia($post->getFirstMedia('posts'));

        return response()->json(['status' => true, 'message' => __('post.success_image_delete')]);
    }

    public function media($type, Posts $post): SymfonyResponse
    {
        return PanelResponse::render(
            'Posts/Media',
            'panel.post.media',
            [
                'type' => $type,
                'post' => ['id' => $post->id, 'title' => $post->title],
                'media' => $post->getMedia('content_images')
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->file_name,
                        'size' => $item->size,
                        'url' => $item->getFullUrl(),
                        'thumb' => mediaConversionUrl($item, 'resized'),
                        'createdAt' => $item->created_at?->toIso8601String(),
                    ])
                    ->values(),
            ],
            ['post' => $post, 'type' => $type],
        );
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function editorImageUpload($type, Posts $post, Request $request)
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
        ]);

        if ($request->slug == null) {
            $slug = Str::slug($request->post('title'));
        } else {
            $slug = Str::slug($request->post('slug'));
        }

        if (! $post->id) {
            $post->title = GetPost($request->post('title')).' (draft)';
            $post->slug = $slug;
            $post->content = content($request->post('content'));
            $post->post_type = GetPost($request->post('post_type'));
            $post->language = $request->post('language');
            $post->user_id = auth()->user()->id;
            $post->meta_keywords = $request->post('meta_keywords');
            $post->save();
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $ext = $request->file('file')->extension();
            $post->addMediaFromRequest('file')
                ->usingFileName($slug.'-'.time().'.'.$ext)
                ->toMediaCollection('content_images');
        }

        return response()->json([
            'success' => true,
            'blog_id' => $post->id,
            'location' => mediaConversionUrl($post->getMedia('content_images')->last(), 'resized'),
        ]);
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function postImageDelete($type, Posts $post, Request $request): JsonResponse
    {
        $post->deleteMedia($request->post('media_id'));

        return response()->json([
            'success' => true,
        ]);
    }
}

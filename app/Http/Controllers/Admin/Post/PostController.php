<?php

namespace App\Http\Controllers\Admin\Post;

use App\Actions\CacheClear;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostRequest;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
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
    /**
     * Inertia her XHR ziyaretinde X-Requested-With: XMLHttpRequest gonderir ve
     * Request::ajax() tam olarak bunu kontrol eder. Salt ajax() ile dallanmak,
     * sidebar uzerinden yapilan her Inertia gezinmesine sayfa yerine DataTables
     * JSON'u dondururdu. jQuery DataTables her zaman `draw` gonderdigi icin bu
     * ek kosul eski davranis acisindan notrdur.
     */
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

    /**
     * Inertia sayfasi icin satirlar.
     *
     * Eski jQuery DataTables ucu (admin.posts + `draw`) DOKUNULMADAN duruyor:
     * henuz tasinmamis Blade ekrani ve dis cagiranlar icin. Burasi ayni veriyi
     * HAM ALANLAR halinde dondurur; HTML kolonlari (checkbox/title/categories/
     * media/action) Vue tarafinda uretilir.
     */
    private function rows(Request $request, $query, ?string $language)
    {
        $search = trim((string) $request->get('search'));
        $perPage = $this->perPage($request);

        if ($search !== '') {
            /*
             * Scout aramasi ilgi sirasi dondurur; orderBy yok sayilir, bu yuzden
             * arama etkinken sıralama UI'i kapatilir (yajra da boyle davraniyordu).
             *
             * DIKKAT: `where('language', ...)` Meilisearch tarafinda bir FILTRE'ye
             * cevrilir; `language` filterableAttributes icinde degilse sessizce
             * etkisiz kalir. phpunit SCOUT_DRIVER=null oldugu icin bunu hicbir test
             * yakalamaz - canli indekse karsi elle dogrulanmali.
             */
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
            // ISO-8601: eski uc created_at icin 'Y-m-d H:i:s', updated_at icin
            // 'd.m.Y H:i:s' basiyordu (tutarsiz). Bicimlendirme artik istemcide.
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
            'deleted_at' => $item->deleted_at?->toIso8601String(),
            'can' => [
                'edit' => $user?->can('edit', $item) ?? false,
                'delete' => $user?->can('delete', $item) ?? false,
            ],
        ];
    }

    /**
     * Sayfa boyu artik session('post_datatable_length') degil acik bir query
     * parametresi. Eski session yazimi, onu okuyan son Blade tablosu da
     * tasinana kadar yerinde birakildi.
     */
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
            // QR okuma sayaci: index()/search yolunda withCount var, editorde YOKTU;
            // bu yuzden editorPost() rozeti her zaman 0 gosteriyordu.
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
                    // Eski blade secenek etiketine dil sonekini basiyordu.
                    'language' => $category->language,
                ])->values(),
                /*
                 * User::all() sinirsiz (bkz. B9). Davranisi degistirmemek icin
                 * liste aynen doner, yalniz yuk azaltildi: tum model yerine
                 * id + nickname.
                 */
                'users' => User::query()
                    ->orderBy('nickname')
                    ->get(['id', 'nickname'])
                    ->map(fn (User $user) => ['id' => (string) $user->id, 'nickname' => $user->nickname])
                    ->values(),
                /*
                 * `languages` DEGIL, `languageOptions` - bkz. MenuController::index()
                 * `menuRecord`. Paylasilan `languages` prop'u (bayrakli ust bar dil
                 * secicisi) ayni adli sayfa prop'u tarafindan EZILIR.
                 */
                'languageOptions' => collect(app('languages'))
                    ->map(fn ($language) => ['code' => $language->code, 'name' => $language->name])
                    ->values(),
                'sessionLanguage' => session('language'),
            ],
            [
                'post' => $post,
                'categories' => $categories,
                'users' => User::all(),
                'type' => $type,
            ],
        );
    }

    /**
     * Editor icin post prop'u.
     *
     * DIKKAT - `language_code` BILEREK gonderilmiyor.
     * PostRequest slug benzersizligini `$this->input('language_code')` ile
     * scope'luyor ama eski form bu alani HIC gondermiyordu; kural fiilen
     * `where language is null` olarak calisiyor ve benzersizlik uygulanmiyor.
     * Bu davranisi korumak icin alan eklenmedi; duzeltmek dogrulama davranisini
     * degistirir ve ayri bir karar gerektirir.
     *
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
            // Eski blade QR'i CDN'den yuklenen qrcodejs ile ciziyordu. Yeni
            // bagimlilik eklemek yerine sunucuda SVG uretilir (Fortify 2FA
            // ekraninda zaten kullanilan bacon/bacon-qr-code ile, ayni data-URI
            // deseninde).
            'qr_image' => self::qrImage($post->qr_link ?? null),
            'history_count' => $post->relationLoaded('history') ? $post->history->count() : 0,
            'comments_count' => $post->relationLoaded('comments') ? $post->comments->count() : 0,
            // datetime-local girdisi icin saniyesiz yerel bicim.
            'published_at' => $post->created_at
                ? $post->created_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i')
                : now()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
        ];
    }

    /**
     * QR baglantisini data-URI SVG'ye cevirir; baglanti yoksa ya da uretici
     * kullanilamiyorsa null doner (editor ekrani bu yuzden asla patlamaz).
     */
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
            // SECURITY: only owner/admin may assign post ownership to another user.
            // Lower-privilege authors cannot spoof the author field.
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
            /*
             * `hreflang_url` GONDERILMEYEBILIR.
             *
             * Vue formu bunu bos nesne olarak baslatiyor; Inertia'nin
             * objectToFormData'si bos nesne icin SIFIR alan ekliyor, yani
             * anahtar govdede hic yer almiyor. Korumasiz foreach null uzerinde
             * ErrorException firlatiyordu -> yeni yazi/sayfa/kategori
             * kaydedilemiyor, mevcut kayitta hreflang bos ise duzenleme de
             * kaydedilemiyor.
             */
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

                /*
                 * Form eylemi: TEK donus sekli yonlendirmedir (R2). Yeni kayitta
                 * hedef .../{id}/edit; eski JSON dali `id` donuyordu, yonlendirme
                 * ayni yere gittigi icin bilgi kaybi yok.
                 */
                return to_route('admin.post.edit', ['type' => $type, 'post' => $post->id])
                    ->with('success', $message);
            }

            // Bu yol commit GORMUYOR: transaction acik kalirdi.
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

            // Bu yol commit GORMUYOR: transaction acik kalirdi.
            DB::rollBack();

            return back()->with('error', __('post.post.error_delete'));
        } catch (Exception $exception) {
            DB::rollBack();

            return back()->with('error', $exception->getMessage());
        }
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

            // Bu yol commit GORMUYOR: transaction acik kalirdi.
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

            // Bu yol commit GORMUYOR: transaction acik kalirdi.
            DB::rollBack();

            return back()->with('error', __('post.post.error_restore'));
        } catch (Exception $exception) {
            DB::rollBack();

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * VERI ucu: Posts/Edit.vue `removeImage()` bunu axios ile cagirir ve yaniti
     * okuyup ekrani `only: ['post']` ile tazeler. Tek donus sekli JSON'dur -
     * X-Inertia basligina gore dallanmak, baslik yolda dustugunde Inertia'ya
     * sayfa yerine JSON verip tam ekran hata modali aciyordu.
     *
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
     * VERI ucu: Posts/Media.vue `destroy()` bunu axios ile cagirir ve listeyi
     * `only: ['media']` ile tazeler. Tek donus sekli JSON'dur - bkz.
     * imageDelete().
     *
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

<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryDeleteRequest;
use App\Http\Requests\Category\CategoryRequest;
use App\Models\Languages;
use App\Models\Post\Categories;
use App\Support\Panel\PanelResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategoryController extends Controller
{
    public function index(Categories $category): Response
    {
        /*
         * `?tab=<dil kodu>` bir EKRAN SOZLESMESI: hem dil sekmeleri hem komut
         * paleti bu parametreyle geliyor. Eski blade tum dilleri birden render
         * edip goruneni `request()->get('tab')` ile seciyordu; Vue portu tek dil
         * gonderdigi icin parametre sunucuda cozulmeli, yoksa sekmeler no-op olur.
         * Bilinmeyen kod yok sayilir (oturum diline duser).
         */
        if ($category->id) {
            $language = $category->language;
        } else {
            $language = self::requestedLanguage(request()->query('tab')) ?? session('language');
        }

        $category->load('media', 'media.model');

        return PanelResponse::render(
            'Categories/Index',
            'panel.post.category.index',
            [
                'language' => $language,
                /*
                 * Blade'e BOS bir `new Categories` ornegi gecirilip sorgular
                 * icerideydi ($categories->where(...)->get()). Bos model JSON'a
                 * serilestirilemez; agac ve duz liste burada cozulur.
                 */
                'tree' => self::tree($language),
                'flat' => Categories::where('language', $language)
                    ->orderBy('name')
                    ->get(['id', 'name', 'parent_id'])
                    ->map(fn (Categories $item) => [
                        'id' => (string) $item->id,
                        'name' => $item->name,
                        'parent_id' => $item->parent_id ? (string) $item->parent_id : null,
                    ])->values(),
                'category' => $category->id ? self::editable($category) : null,
                /*
                 * `languages` DEGIL, `languageOptions` - bkz. MenuController::index()
                 * `menuRecord`. Paylasilan `languages` prop'u (bayrakli ust bar dil
                 * secicisi) ayni adli sayfa prop'u tarafindan EZILIR.
                 */
                'languageOptions' => collect(app('languages'))
                    ->map(fn ($item) => ['code' => $item->code, 'name' => $item->name])
                    ->values(),
            ],
            [
                'categories' => new Categories,
                'category' => $category,
                'lng' => $language,
            ],
        );
    }

    /**
     * `?tab` degerini yalnizca TANIMLI bir dil kodu ise kabul eder.
     */
    private static function requestedLanguage(mixed $tab): ?string
    {
        if (! is_string($tab) || $tab === '') {
            return null;
        }

        $known = collect(app()->bound('languages') ? app('languages') : Languages::all())
            ->pluck('code')
            ->all();

        return in_array($tab, $known, true) ? $tab : null;
    }

    /**
     * Kategori agaci (blade'deki ozyinelemeli category_row partial'inin karsiligi).
     *
     * @return list<array<string, mixed>>
     */
    private static function tree(?string $language, ?int $parentId = null): array
    {
        return Categories::where('language', $language)
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get()
            ->map(fn (Categories $item) => [
                'id' => (string) $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'children' => self::tree($language, $item->id),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function editable(Categories $category): array
    {
        return [
            'id' => (string) $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'language' => $category->language,
            'parent_id' => $category->parent_id ? (string) $category->parent_id : null,
            'meta_description' => $category->meta_description,
            'meta_keywords' => $category->meta_keywords,
            'hreflang' => $category->href_lang ? (json_decode($category->href_lang, true) ?: []) : [],
            'image' => $category->getFirstMediaUrl('categories') ?: null,
        ];
    }

    public function store(CategoryRequest $request, Categories $category): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();
            if ($category->id) {
                $message = __('categories.success_update');
            } else {
                $message = __('categories.success');
            }
            $category->name = GetPost($request->name);
            if ($request->slug == null) {
                $category->slug = Str::slug($request->name);
            } else {
                $category->slug = Str::slug($request->slug);
            }
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $category->addMediaFromRequest('image')->toMediaCollection('categories');
            }
            $category->meta_description = GetPost($request->meta_description);
            $category->meta_keywords = GetPost($request->meta_keywords);
            $category->language = GetPost($request->language);
            $category->parent_id = GetPost($request->parent_id);
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
            $category->href_lang = json_encode($hreflang);
            if ($category->save()) {
                DB::commit();

                return $request->inertia()
                    ? back()->with('success', $message)
                    : response()->json(['status' => 'success', 'message' => $message]);
            }

            // Bu yol commit GORMUYOR: transaction acik kalirdi.
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('categories.error'))
                : response()->json(['status' => 'error', 'message' => __('categories.error')]);
        } catch (Throwable $exception) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', $exception->getMessage())
                : response()->json(['status' => 'error', 'message' => $exception->getMessage()])->setStatusCode(500);
        }
    }

    public function delete(CategoryDeleteRequest $request, Categories $category): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();
            if ($category::find($request->id)->delete()) {
                DB::commit();

                return $request->inertia()
                    ? back()->with('success', __('categories.success_delete'))
                    : response()->json(['status' => 'success', 'message' => __('categories.success_delete')]);
            }

            return $request->inertia()
                ? back()->with('error', __('categories.error_delete'))
                : response()->json(['status' => 'error', 'message' => __('categories.error_delete')]);
        } catch (Throwable $exception) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', $exception->getMessage())
                : response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function deleteImage(Request $request)
    {
        try {
            DB::beginTransaction();
            $image = Categories::find($request->post('id'));

            /*
             * `find()` null donebiliyor; ardindan gelen metot cagrisi `Error`
             * firlatir ve asagidaki `catch (Exception)` onu YAKALAMAZ —
             * sonuc: 500 + acik kalan transaction (istek boyunca kilit).
             */
            if (! $image) {
                DB::rollBack();

                return $request->inertia()
                    ? back()->with('error', __('categories.error'))
                    : response()->json(['status' => 'error'], 404);
            }

            $image->deleteMedia($image->getFirstMedia('categories'));
            DB::commit();

            return $request->inertia()
                ? back()->with('success', __('post.success_image_delete'))
                : response()->json(['status' => 'success']);
        } catch (Throwable $exception) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', $exception->getMessage())
                : response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function categoryList(Request $request): JsonResponse
    {
        return response()->json(Categories::where('language', $request->language)->get());
    }
}

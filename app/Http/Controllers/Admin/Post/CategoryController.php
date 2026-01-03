<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryDeleteRequest;
use App\Http\Requests\Category\CategoryRequest;
use App\Models\Post\Categories;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Categories $category): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        if ($category->id) {
            $language = $category->language;
        } else {
            $language = session('language');
        }

        return view('panel.post.category.index', [
            'categories' => new Categories,
            'category' => $category->load('media', 'media.model'),
            'lng' => $language,
        ]);
    }

    public function store(CategoryRequest $request, Categories $category): JsonResponse
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
            foreach ($request->hreflang_url as $key => $value) {
                if ($value != null) {
                    $hreflang[$key] = GetPost($value);
                }
            }
            $category->href_lang = json_encode($hreflang);
            if ($category->save()) {
                DB::commit();

                return response()->json(['status' => 'success', 'message' => $message]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('categories.error')]);
            }
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()])->setStatusCode(500);
        }
    }

    public function delete(CategoryDeleteRequest $request, Categories $category): JsonResponse
    {
        try {
            DB::beginTransaction();
            if ($category::find($request->id)->delete()) {
                DB::commit();

                return response()->json(['status' => 'success', 'message' => __('categories.success_delete')]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('categories.error_delete')]);
            }
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function deleteImage(Request $request)
    {
        try {
            DB::beginTransaction();
            $image = Categories::find($request->post('id'));
            $image->deleteMedia($image->getFirstMedia('categories'));
            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function categoryList(Request $request): JsonResponse
    {
        return response()->json(Categories::where('language', $request->language)->get());
    }
}

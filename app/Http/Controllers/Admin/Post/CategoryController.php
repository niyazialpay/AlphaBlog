<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryDeleteRequest;
use App\Http\Requests\Category\CategoryRequest;
use App\Models\Categories;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Categories $category): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('panel.post.category.index', [
            'categories' => new Categories(),
            'category' => $category,
        ]);
    }

    public function store(CategoryRequest $request, Categories $category): JsonResponse
    {
        if($category->id){
            $message = __('categories.success_update');
        }
        else{
            $message = __('categories.success');
        }
        $category->name = GetPost($request->name);
        if($request->slug == null) {
            $category->slug = Str::slug($request->name);
        }
        else{
            $category->slug =  Str::slug($request->slug);
        }
        $category->meta_description = GetPost($request->meta_description);
        $category->meta_keywords = GetPost($request->meta_keywords);
        $category->language = GetPost($request->language);
        $hreflang = [];
        foreach($request->hreflang_url as $key => $value){
            if($value != null) {
                $hreflang[$key] = GetPost($value);
            }
        }
        $category->href_lang = $hreflang;
        if($category->save()){
            return response()->json(['status' => 'success', 'message' => $message]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('categories.error')]);
        }
    }

    public function delete(CategoryDeleteRequest $request, Categories $category): JsonResponse
    {
        if($category::find($request->id)->delete()){
            return response()->json(['status' => 'success', 'message' => __('categories.success_delete')]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => __('categories.error_delete')]);
        }
    }
}

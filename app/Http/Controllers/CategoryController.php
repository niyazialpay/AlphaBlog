<?php

namespace App\Http\Controllers;

use App\Models\Post\Categories;
use Exception;

class CategoryController extends Controller
{
    public function show($language, $categories, $showCategory){
        try{
            return response()->view(app('theme')->name.'.categories', [
                'category' => $showCategory
            ]);
        }
        catch (Exception $e){
            return response()->view('Default.categories', [
                'category' => $showCategory
            ]);
        }
    }
}

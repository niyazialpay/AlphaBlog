<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function show($language, $tags, $showTag){
        try{
            return response()->view(app('theme')->name.'.tags', [
                'posts' => $showTag,
                'tag' => request()->segment(3)
            ]);
        }
        catch (Exception $e){
            return response()->view('Default.tags', [
                'posts' => $showTag,
                'tag' => request()->segment(3)
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Cookie;

class PostController extends Controller
{
    public function show($language, $showPost){
        if(!Cookie::has($showPost->slug)){
            $showPost->increment('views');
            $showPost->save();
            Cookie::queue(Cookie::make($showPost->slug, true, 7200));
        }
        try{
            return response()->view('themes.'.app('theme')->name.'.post', [
                'post' => $showPost
            ]);
        }
        catch (Exception $e){
            return response()->view('Default.post', [
                'post' => $showPost
            ]);
        }
    }
}

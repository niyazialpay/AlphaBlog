<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CDNController extends Controller
{
    public function index($any=null){
        if($any == null){
            abort(404);
        }
        $path = public_path($any);

        if (file_exists($path) && is_file($path)) {
            return response()->file($path);
        }

        abort(404);
    }
}

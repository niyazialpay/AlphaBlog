<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CDNController extends Controller
{
    public function index($any){
        $path = public_path($any);
        if (file_exists($path)) {
            return response()->file($path);
        }
        abort(404);
    }
}

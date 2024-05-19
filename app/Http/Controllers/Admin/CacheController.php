<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function clearCache()
    {
        if(Cache::flush()){
            return redirect()->back()->with('success', __('cache.cache_cleared'));
        }
        else{
            return redirect()->back()->with('error', __('cache.cache_not_cleared'));
        }
    }
}

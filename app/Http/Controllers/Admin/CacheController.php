<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function clearCache()
    {
        if (Cache::flush()) {
            return response()->json([
                'status' => 'success',
                'message' => __('cache.cache_cleared'),
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('cache.cache_not_cleared'),
            ]);
        }
    }
}

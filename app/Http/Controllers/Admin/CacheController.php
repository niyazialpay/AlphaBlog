<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CacheClear;
use App\Http\Controllers\Controller;
use Cloudflare\API\Endpoints\EndpointException;


class CacheController extends Controller
{
    /**
     * @throws EndpointException
     */
    public function clearCache()
    {
        if (CacheClear::cacheClear()) {
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

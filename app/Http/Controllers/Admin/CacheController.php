<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cloudflare;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\EndpointException;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    /**
     * @throws EndpointException
     */
    public function clearCache()
    {
        if (Cache::flush()) {
            $cf = Cloudflare::first();
            if ($cf) {
                $key = new APIKey($cf->cf_email, $cf->cf_key);
                $adapter = new Guzzle($key);
                $zones = new Zones($adapter);
                $zoneID = $zones->getZoneID($cf->domain);
                $zones->cachePurgeEverything($zoneID);
            }

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

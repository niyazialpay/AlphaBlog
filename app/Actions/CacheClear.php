<?php

namespace App\Actions;

use App\Models\Cloudflare;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\EndpointException;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Support\Facades\Cache;

class CacheClear
{

    /**
     * @throws EndpointException
     */
    public static function cacheClear(): bool
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

            return true;
        }
        return false;
    }
}
